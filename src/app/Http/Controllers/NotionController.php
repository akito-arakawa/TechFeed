<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotionService;
use App\Services\NotionAuthService;

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
            $query = http_build_query([
                'client_id' => config('services.notion.client_id'),
                'response_type' => 'code',
                'owner' => 'user',
                'redirect_uri' => config('services.notion.redirect_uri'),
            ]);
            $redirectUrl = "https://api.notion.com/v1/oauth/authorize?" . $query;

            return response()->json([
                'message' => 'NOTION_NOT_CONNECTED',
                'redirectUrl' => $redirectUrl,
            ], 401);
        }
    }
}
