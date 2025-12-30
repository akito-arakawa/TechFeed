<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ViewService;
use Illuminate\Support\Facades\Log;

class ArticleViewController extends Controller
{
    public function __construct(
        private ViewService $viewService
    ) {
    }
    public function store(Article $article)
    {
        try {
            $user = auth()->user();
            $this->viewService->viewHistory($user, $article->id);
            return response()->json([
                'message' => 'success',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }
}
