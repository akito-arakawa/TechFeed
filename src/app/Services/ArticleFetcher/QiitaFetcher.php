<?php
namespace App\Services\ArticleFetcher;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use App\Models\Source;

class QiitaFetcher
{
    private string $baseUrl = 'https://qiita.com/api/v2/items';
    private string $defaultQiitaThumbnail;

    public function __construct()
    {
        $this->defaultQiitaThumbnail = url('images/qiita.png');
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
        $new = 0;
        $updated = 0;
        $skipped = 0;
        $source = Source::where('name', 'Qiita')->first();

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
                break;
            }

            $items = $response->json();
            if (empty($items)) {
                break; // これ以上記事がない
            }

            $mergedItems = array_merge($mergedItems, $items);
        }

        // まとめて保存
        foreach ($mergedItems as $item) {
            $article = Article::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'source_item_id' => $item['id'],
                ],
                [
                    'url' => $item['url'],
                    'title' => $item['title'],
                    'author_name' => $item['user']['id'] ?? null,
                    'thumbnail_url' => $this->defaultQiitaThumbnail,
                    'source_like_count' => $item['likes_count'] ?? 0,
                    'pubished_at' => $item['created_at'],
                    'fetched_at' => now(),
                ]
            );

            $article->wasRecentlyCreated ? $new++ : $updated++;
        }

        return [$new, $updated, $skipped];
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

    // APIアクセス＋保存
    public function fetchFromApi(array $params): array
    {
        $new = 0;
        $updated = 0;
        $skipped = 0;

        $response = HTTP::get($this->baseUrl, $params);

        if ($response->failed()) {
            throw new \RuntimeException('Qiita API error:' . $response->status());
        }

        $items = $response->json();
        $source = Source::where('name', 'Qiita')->first();

        // すでに取得済みのデータは更新・新規取得は登録
        foreach ($items as $item) {
            $article = Article::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'source_item_id' => $item['id'],
                ],
                [
                    'url' => $item['url'],
                    'title' => $item['title'],
                    'author_name' => $item['user']['id'] ?? null,
                    'thumbnail_url' => $this->defaultQiitaThumbnail,
                    'source_like_count' => $item['likes_count'] ?? 0,
                    'pubished_at' => $item['created_at'],
                    'fetched_at' => now(),
                ]
            );

            $article->wasRecentlyCreated ? $new++ : $updated++;
        }

        return [$new, $updated, $skipped];
    }
}