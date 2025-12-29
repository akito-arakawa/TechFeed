<?php
namespace App\Services\ArticleFetcher;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use App\Models\Source;
use App\Models\Category;
use App\Models\ArticleCategory;

class ZennFetcher
{
    private string $baseUrl = 'https://zenn.dev/api/articles';
    private string $defaultZennThumbnail;

    public function __construct()
    {
        $this->defaultZennThumbnail = url('images/zenn.png');
    }

    // 新着記事
    public function fetchNew(): array
    {
        return $this->fetchFromApi([
            'order' => 'latest',
            'count' => 100,
        ]);
    }

    // 人気記事
    public function fetchPopular(string $term = 'weekly'): array
    {
        return $this->fetchFromApi([
            'order' => $term, //weekly or alltime
            'count' => 100,
        ]);
    }

    // タグ別記事
    public function fetchByTag(string $tag): array
    {
        return $this->fetchFromApi([
            'topicname' => $tag,
            'count' => 100,
        ]);
    }

    // 記事取得API
    public function fetchFromApi(array $params): array
    {
        $new = 0;
        $updated = 0;

        $response = HTTP::get($this->baseUrl, $params);

        if ($response->failed()) {
            throw new \RuntimeException('Zenn API error:' . $response->status());
        }

        $json = $response->json();
        $items = $json['articles'] ?? [];
        $source = Source::where('name', 'Zenn')->first();

        foreach ($items as $item) {
            $article = Article::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'source_item_id' => $item['id'],
                ],
                [
                    'url' => "https://zenn.dev/" . ltrim($item['path'], '/'),
                    'title' => $item['title'],
                    'author_name' => $item['user']['username'] ?? null,
                    'thumbnail_url' => $item['emoji'] ?? $this->defaultZennThumbnail,
                    'source_like_count' => $item['liked_count'] ?? 0,
                    'published_at' => $item['published_at'],
                    'fetched_at' => now(),
                ]
            );

            // カテゴリとの関連づけ
            $url = $this->baseUrl . '/' . $item['slug'];
            $itemResponse = Http::get($url);
            if ($itemResponse->failed()) {
                throw new \RuntimeException('Zenn API error:' . $response->status());
            }

            $itemsJson = $itemResponse->json();

            $topics = $itemsJson['article']['topics'] ?? [];
            foreach ($topics as $topic) {
                $category = Category::where('slug', $topic['name'])->first();
                if ($category) {
                    $article->categories()->syncWithoutDetaching([
                        $category->id
                    ]);
                }
            }

            $article->wasRecentlyCreated ? $new++ : $updated++;
        }

        return [$new, $updated];
    }

}
