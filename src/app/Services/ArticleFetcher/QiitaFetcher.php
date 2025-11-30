<?php
namespace App\Services\ArticleFetcher;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use App\Models\Source;
use App\Models\Category;
use App\Models\ArticleCategory;

class QiitaFetcher
{
    private string $baseUrl = 'https://qiita.com/api/v2/items';
    private string $defaultQiitaThumbnail;
    private Source $source;

    public function __construct()
    {
        $this->defaultQiitaThumbnail = url('images/qiita.png');
        $this->source = Source::where('name', 'Qiita')->first();
    }

    // 新着記事
    public function fetchNew(): array
    {
        return $this->fetchFromApi(
            [
                'page' => 1,
                'per_page' => 20,
            ]
        );
    }
    // 人気記事
    public function fetchPopular(): array
    {
        $mergedItems = [];

        // Qiita API は page 1〜100 まで
        for ($page = 1; $page <= 50; $page++) {

            $response = Http::get($this->baseUrl, [
                'page' => $page,
                'per_page' => 100,
                // ストックが40以上のものだけを取得
                'query' => 'stocks:>40',
            ]);

            if ($response->failed()) {
                throw new \RuntimeException("Qiita API Error: " . $response->status());
            }

            $items = $response->json();
            if (empty($items)) {
                break; // これ以上記事がない
            }

            $mergedItems = array_merge($mergedItems, $items);
        }

        // まとめて保存
        return $this->fetchFromApi($mergedItems);
    }

    // カテゴリ別記事
    public function fetchByTag(string $tag): array
    {
        return $this->fetchFromApi([
            'page' => 1,
            'per_page' => 20,
            'query' => "tag:{$tag}",
        ]);
    }

    // APIアクセス
    public function fetchFromApi(array $params): array
    {
        $response = HTTP::get($this->baseUrl, $params);

        if ($response->failed()) {
            throw new \RuntimeException('Qiita API error:' . $response->status());
        }

        return $this->saveArticles($response->json());
    }

    // 記事保存
    public function saveArticles(array $items): array
    {
        $new = 0;
        $updated = 0;

        foreach ($items as $item) {
            $article = Article::updateOrCreate(
                [
                    'source_id' => $this->source->id,
                    'source_item_id' => $item['id'],
                ],
                [
                    'url' => $item['url'],
                    'title' => $item['title'],
                    'author_name' => $item['user']['id'] ?? null,
                    'thumbnail_url' => $this->defaultQiitaThumbnail,
                    'source_like_count' => $item['likes_count'] ?? 0,
                    'published_at' => $item['created_at'],
                    'fetched_at' => now(),
                ]
            );

            $this->saveArticleCategory($item, $article);

            $article->wasRecentlyCreated ? $new++ : $updated++;
        }

        return [$new, $updated];
    }

    // カテゴリ紐づけ
    public function saveArticleCategory($item, $article)
    {
        $tags = $item['tags'] ?? [];

        foreach ($tags as $tag) {
            $category = Category::where('slug', $tag['name'])
                ->where('name', $tag['name'])
                ->first();

            if (isset($category)) {
                ArticleCategory::create([
                    'article_id' => $article['id'],
                    'category_id' => $category['id'],
                ]);
            }
        }
    }
}
