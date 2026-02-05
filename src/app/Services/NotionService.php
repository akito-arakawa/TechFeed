<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use App\Models\UserNotionToken;
use Illuminate\Support\Facades\Log;

class NotionService
{
    private const NOTION_API = 'https://api.notion.com/v1';
    private const NOTION_VERSION_LATEST = '2025-09-03';

    /**
     * ユーザーのNotionトークンの保存
     * @param array $response Notionの認証コールバックから取得したトークン情報
     * @param int $userId トークンを保存するユーザーID（callback は Bearer を送れないため state で特定した ID を渡す）
     */
    public function saveToken(array $response, int $userId): void
    {
        UserNotionToken::updateOrCreate(
            ['user_id' => $userId],
            [
                'user_id' => $userId,
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'notion_workspace_id' => $response['workspace_id'],
                'notion_workspace_name' => $response['workspace_name'],
                'notion_bot_id' => $response['bot_id'],
            ]
        );
    }
    /**
     * ユーザーのNotionに「TechFeed」データベースを取得または作成し、そのIDを返す
     *
     * @param string $accessToken ユーザーのNotionアクセストークン
     * @param string|null $parentPageId データベースを作成する親ページID（未作成時のみ必要）
     * @return string|null データベースID。親ページが未設定かつ未検出の場合は null
     */
    public function getOrCreateTechFeedDatabase(string $accessToken, ?string $parentPageId): ?string
    {
        $dbTitle = config('services.notion.database_title', 'TechFeed');

        $existing = $this->searchDatabaseByTitle($accessToken, $dbTitle);
        if ($existing !== null) {
            return $existing;
        }

        if (empty($parentPageId)) {
            return null;
        }

        return $this->createDatabase($accessToken, $parentPageId, $dbTitle);
    }

    /**
     * Notionワークスペース直下に親ページを作成する
     * @param string $accessToken ユーザーのNotionアクセストークン
     * @param string $pageTitle ページタイトル
     * @return string|null 作成されたページID。失敗時は null
     */
    public function createParentPage(string $accessToken, string $pageTitle = 'TechFeed'): ?string
    {
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Notion-Version' => self::NOTION_VERSION_LATEST,
            ])
            ->post(self::NOTION_API . '/pages', [
                'parent' => ['type' => 'workspace', 'workspace' => true],
                'properties' => [
                    'title' => [
                        [
                            'type' => 'text',
                            'text' => ['content' => $pageTitle],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Notion parent page creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return null;
        }

        $body = $response->json();
        return $body['id'] ?? null;
    }

    /**
     * TechFeedデータベースに記事を1件追加する
     *
     * @param string $accessToken ユーザーのNotionアクセストークン
     * @param string $databaseId TechFeedデータベースのID
     * @param Article $article 対象記事
     * @return array{ok: bool, page_id?: string, error?: string}
     */
    public function addArticleToNotion(string $accessToken, string $databaseId, Article $article): array
    {
            $article->load(['source', 'categories']);

            $titleContent = mb_substr($article->title, 0, 2000);

        $properties = [
            'Title' => [
                'title' => [
                    ['text' => ['content' => $titleContent]],
                ],
            ],
            'URL' => [
                'url' => $article->url,
            ],
        ];

        // データベースにURL・出典・カテゴリ・公開日などのプロパティがある場合はスキーマに依存するため、
        // 本文ブロックで情報を載せる
        $children = $this->buildPageChildren($article);

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Notion-Version' => self::NOTION_VERSION_LATEST,
            ])
            ->post(self::NOTION_API . '/pages', [
                'parent' => ['database_id' => $databaseId],
                'properties' => $properties,
                'children' => $children,
            ]);

        if (!$response->successful()) {
            return ['ok' => false, 'error' => 'create_failed', 'body' => $response->json()];
        }

        $body = $response->json();
        return ['ok' => true, 'page_id' => $body['id'] ?? null];
    }

    private function searchDatabaseByTitle(string $accessToken, string $title): ?string
    {
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Notion-Version' => self::NOTION_VERSION_LATEST,
            ])
            ->post(self::NOTION_API . '/search', [
                'query' => $title,
                'filter' => ['property' => 'object', 'value' => 'database'],
                'page_size' => 1,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        $results = $data['results'] ?? [];

        // query と filter で「タイトルに TechFeed を含む database」に絞っているため、
        // 1件でも返っていればその id を返す。Search API のレスポンスでは properties が
        // 省略されることがあるため、ここではタイトル再照合は行わない。
        $first = $results[0] ?? null;
        return $first !== null ? ($first['id'] ?? null) : null;
    }

    private function createDatabase(string $accessToken, string $parentPageId, string $title): ?string
    {
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Notion-Version' => self::NOTION_VERSION_LATEST,
            ])
            ->post(self::NOTION_API . '/databases', [
                'parent' => ['type' => 'page_id', 'page_id' => $this->normalizeNotionId($parentPageId)],
                'title' => [
                    ['type' => 'text', 'text' => ['content' => $title]],
                ],
                'initial_data_source' => [
                    'properties' => [
                        'Title' => ['title' => (object)[]],
                        'URL' => ['url' => (object)[]],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();
        return $body['id'] ?? null;
    }

    private function buildPageChildren(Article $article): array
    {
        $blocks = [];

        $blocks[] = [
            'object' => 'block',
            'type' => 'heading_2',
            'heading_2' => [
                'rich_text' => [['type' => 'text', 'text' => ['content' => 'リンク']]],
            ],
        ];
        $blocks[] = [
            'object' => 'block',
            'type' => 'paragraph',
            'paragraph' => [
                'rich_text' => [
                    [
                        'type' => 'text',
                        'text' => ['content' => $article->url, 'link' => ['url' => $article->url]],
                    ],
                ],
            ],
        ];

        $blocks[] = [
            'object' => 'block',
            'type' => 'heading_2',
            'heading_2' => [
                'rich_text' => [['type' => 'text', 'text' => ['content' => '出典']]],
            ],
        ];
        $sourceName = $article->source?->name ?? '—';
        $blocks[] = [
            'object' => 'block',
            'type' => 'paragraph',
            'paragraph' => [
                'rich_text' => [['type' => 'text', 'text' => ['content' => $sourceName]]],
            ],
        ];

        $cats = $article->categories->pluck('name')->implode(', ');
        if ($cats !== '') {
            $blocks[] = [
                'object' => 'block',
                'type' => 'heading_2',
                'heading_2' => [
                    'rich_text' => [['type' => 'text', 'text' => ['content' => 'カテゴリ']]],
                ],
            ];
            $blocks[] = [
                'object' => 'block',
                'type' => 'paragraph',
                'paragraph' => [
                    'rich_text' => [['type' => 'text', 'text' => ['content' => $cats]]],
                ],
            ];
        }

        $blocks[] = [
            'object' => 'block',
            'type' => 'heading_2',
            'heading_2' => [
                'rich_text' => [['type' => 'text', 'text' => ['content' => '公開日']]],
            ],
        ];
        $published = $article->published_at?->format('Y-m-d H:i') ?? '—';
        $blocks[] = [
            'object' => 'block',
            'type' => 'paragraph',
            'paragraph' => [
                'rich_text' => [['type' => 'text', 'text' => ['content' => $published]]],
            ],
        ];

        $blocks[] = [
            'object' => 'block',
            'type' => 'heading_2',
            'heading_2' => [
                'rich_text' => [['type' => 'text', 'text' => ['content' => 'アウトプット・メモ']]],
            ],
        ];
        $blocks[] = [
            'object' => 'block',
            'type' => 'paragraph',
            'paragraph' => [
                'rich_text' => [['type' => 'text', 'text' => ['content' => '（ここに学びやメモを書く）']]],
            ],
        ];

        return $blocks;
    }

    private function normalizeNotionId(string $id): string
    {
        $id = str_replace('-', '', $id);
        if (strlen($id) === 32) {
            return substr($id, 0, 8) . '-' . substr($id, 8, 4) . '-' . substr($id, 12, 4) . '-' . substr($id, 16, 4) . '-' . substr($id, 20, 12);
        }
        return $id;
    }
}
