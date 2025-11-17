<?php
namespace App\Services\ArticleFetcher;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use App\Models\Source;
class ZennFetcher
{
    private string $baseUrl = 'https://zenn.dev/api/articles';
    private string $defaultZennThumbnail;

    public function __construct()
    {
        $this->defaultZennThumbnail = url('images/qiita.png');
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
            'order' => $term, //weeky or monthly
            'count' => 100,
        ]);
    }

    // タグ別記事
    public function fetchByTag(string $tag): array
    {
        return $this->fetchFromApi([
            'topic' => $tag,
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

        $items = $response->json();
        $items = $json['articles'] ?? [];

        $source = Source::where('name', 'Zenn')->first();

        // すでに取得済みのデータは更新・新規取得は登録
        foreach ($items as $item) {
            $article = Article::updateOrCreate(
                [
                    'source_id' => $source->id,
                    'source_item_id' => $item['id'],
                ],
                [
                    'url' => "https://zenn.dev/{$item['path']}",
                    'title' => $item['title'],
                    'author_name' => $item['user']['username'] ?? null,
                    'thumbnail_url' => $item['emoji']
                        ? "https://zenn.dev/images/emojis/{$item['emoji']}.png"
                        : $this->defaultZennThumbnail,
                    'source_like_count' => $item['likes_count'] ?? 0,
                    'pubished_at' => $item['published_at'],
                    'fetched_at' => now(),
                ]
            );

            $article->wasRecentlyCreated ? $new++ : $updated++;
        }

        return [$new, $updated];
    }

}
