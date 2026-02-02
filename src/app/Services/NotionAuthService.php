<?php

namespace App\Services;

use App\Models\UserNotionToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NotionAuthService
{
    private const STATE_CACHE_PREFIX = 'notion_oauth_state:';
    private const STATE_TTL_SECONDS = 600;

    /**
     * 認証ユーザーとその Notion トークンを取得する。
     * 未認証または Notion 未連携の場合は null を返す。
     */
    public function auth(): ?UserNotionToken
    {
        $user = auth()->user();
        if ($user === null) {
            return null;
        }

        $notionToken = $user->notionToken;
        if ($notionToken === null) {
            return null;
        }

        return $notionToken;
    }

    /**
     * Notionの認証コールバックを処理する。
     * @param string $code
     * @return array
     */
    public function getToken(string $code)
    {
        $response = Http::withBasicAuth(
            config('services.notion.client_id'),
            config('services.notion.client_secret')
        )->withHeaders([
            'Content-Type' => 'application/json',
            'Notion-Version' => '2025-09-03',
        ])->post('https://api.notion.com/v1/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.notion.redirect_uri'),
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * OAuth コールバック用の state を発行し、user_id と紐付けてキャッシュする。
     * callback で Bearer が送れないため、state でユーザーを特定する。
     */
    public function createStateForUser(int $userId): string
    {
        $state = Str::random(64);
        $key = self::STATE_CACHE_PREFIX . $state;
        Cache::put($key, $userId, self::STATE_TTL_SECONDS);
        
        return $state;
    }

    /**
     * state から user_id を取得する。1回限り有効（取得後にキャッシュ削除）。
     */
    public function getUserIdFromState(string $state): ?int
    {
        $key = self::STATE_CACHE_PREFIX . $state;
        $userId = Cache::pull($key);
        
        return $userId !== null ? (int) $userId : null;
    }
}
