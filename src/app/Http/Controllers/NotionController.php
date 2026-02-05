<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotionService;
use App\Services\NotionAuthService;
use App\Models\UserNotionToken;
use App\Models\Article;
use App\Http\Requests\NotionOutputRequest;
use Illuminate\Support\Facades\Log;

class NotionController extends Controller
{
    public function __construct(
        private NotionService $notion_service,
        private NotionAuthService $notion_auth_service
    ) {
        $this->notion_service = $notion_service;
        $this->notion_auth_service = $notion_auth_service;
    }

    public function output(NotionOutputRequest $request)
    {
        $notionToken = $this->notion_auth_service->auth();
        if (!$notionToken) {
            return response()->json(['message' => 'NOTION_NOT_CONNECTED'], 401);
        }

        // database_idが設定されていない場合
        if (empty($notionToken->database_id)) {
            Log::warning('Notion database_id is not set', [
                'user_id' => $notionToken->user_id ?? null,
            ]);
            return response()->json(['message' => 'DATABASE_NOT_SET'], 400);
        }

        // バリデーション済みのarticle_idからArticleモデルを取得
        $validated = $request->validated();
        $article = Article::findOrFail($validated['article_id']);

        try {
            $result = $this->notion_service->addArticleToNotion(
                $notionToken->access_token,
                $notionToken->database_id,
                $article
            );

            if (!$result['ok']) {
                Log::warning('Failed to add article to Notion', [
                    'user_id' => $notionToken->user_id ?? null,
                    'article_id' => $article->id,
                    'error' => $result['error'] ?? 'unknown',
                    'body' => $result['body'] ?? null,
                ]);
                return response()->json([
                    'message' => 'NOTION_CREATE_FAILED',
                    'error' => $result['error'] ?? 'unknown',
                ], 500);
            }

            Log::info('Article added to Notion successfully', [
                'user_id' => $notionToken->user_id ?? null,
                'article_id' => $article->id,
                'page_id' => $result['page_id'] ?? null,
            ]);

            return response()->json([
                'message' => 'SUCCESS',
                'page_id' => $result['page_id'] ?? null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Exception occurred while adding article to Notion', [
                'user_id' => $notionToken->user_id ?? null,
                'article_id' => $article->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'INTERNAL_SERVER_ERROR'], 500);
        }
    }

    public function auth()
    {
        // notionの認証を行う
        $state = $this->notion_auth_service->createStateForUser(auth()->user()->id);
        $query = http_build_query([
            'client_id' => config('services.notion.client_id'),
            'response_type' => 'code',
            'owner' => 'user',
            'redirect_uri' => config('services.notion.redirect_uri'),
            'state' => $state,
        ]);
        $redirectUrl = "https://api.notion.com/v1/oauth/authorize?" . $query;

        return response()->json([
            'message' => 'NOTION_NOT_CONNECTED',
            'redirectUrl' => $redirectUrl,
        ], 401);
    }

    public function callback(Request $request)
    {
        $state = $request->query('state');
        $code = $request->query('code');

        if (empty($state)) {
            Log::warning('Notion callback: state parameter is missing or empty');
            return response()->json(['message' => 'MISSING_STATE'], 400);
        }

        $userId = $this->notion_auth_service->getUserIdFromState($state);
        if ($userId === null) {
            Log::warning('Notion callback: invalid or expired state');
            return response()->json(['message' => 'INVALID_OR_EXPIRED_STATE'], 400);
        }

        if (empty($code)) {
            return response()->json(['message' => 'MISSING_CODE'], 400);
        }

        $response = $this->notion_auth_service->getToken($code);
        if ($response === null) {
            return response()->json(['message' => 'TOKEN_EXCHANGE_FAILED'], 400);
        }

        $this->notion_service->saveToken($response, $userId);

        // トークン保存後、親ページを作成してからデータベースを作成
        $notionToken = UserNotionToken::find($userId);
        if (!$notionToken) {
            Log::error('Notion token not found after save', ['user_id' => $userId]);
            return response()->json(['message' => 'TOKEN_SAVE_FAILED'], 500);
        }

        try {
            // 親ページが未設定の場合、新規作成
            if (!filled($notionToken->parent_page_id)) {
                $parentPageId = $this->notion_service->createParentPage(
                    $notionToken->access_token,
                    config('services.notion.parent_page_title', 'TechFeed')
                );

                if ($parentPageId === null) {
                    Log::warning('Notion parent page creation failed', ['user_id' => $userId]);
                    // 親ページ作成失敗でもトークン保存は成功として扱う
                    return response()->json([
                        'message' => 'success',
                        'note' => 'Parent page creation failed. Please set parent_page_id manually.',
                    ], 200);
                }

                // parent_page_idを保存
                $notionToken->parent_page_id = $parentPageId;
                $notionToken->save();

                Log::info('Notion parent page created', [
                    'user_id' => $userId,
                    'parent_page_id' => $parentPageId,
                ]);
            }

            // データベースを作成または取得
            $databaseId = $this->notion_service->getOrCreateTechFeedDatabase(
                $notionToken->access_token,
                $notionToken->parent_page_id
            );

            if ($databaseId !== null) {
                $notionToken->database_id = $databaseId;
                $notionToken->save();
                Log::info('Notion database created or found', [
                    'user_id' => $userId,
                    'database_id' => $databaseId,
                ]);
            } else {
                Log::warning('Notion database creation returned null', [
                    'user_id' => $userId,
                    'parent_page_id' => $notionToken->parent_page_id,
                ]);
            }
        } catch (\Exception $e) {
            // ページ/データベース作成失敗はログに記録するが、トークン保存は成功として扱う
            Log::warning('Notion page/database creation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // レスポンスはフロントのhomeに遷移させる
        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
