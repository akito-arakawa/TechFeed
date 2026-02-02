<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotionService;
use App\Services\NotionAuthService;
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

    public function auth(Request $request)
    {
        // notionの認証を行う
        $notionToken = $this->notion_auth_service->auth();
        if (!$notionToken) {
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

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
