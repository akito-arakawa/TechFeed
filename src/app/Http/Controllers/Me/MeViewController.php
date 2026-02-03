<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\ViewRequest;
use App\Services\ViewService;
use App\Http\Resources\ArticleResource;
use Illuminate\Support\Facades\Log;


class MeViewController extends Controller
{
    public function __construct(
        private ViewService $viewService
    ) {
    }
    public function index(ViewRequest $req)
    {
        try {
            $validated = $req->validated();
            $page = $validated['page'] ?? 1;
            $user = auth()->user();
            $articles = $this->viewService->getViews($user, $page);

            return response()->json(
                ['articles' => ArticleResource::collection($articles)->response()->getData(true)],
            );
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }
}
