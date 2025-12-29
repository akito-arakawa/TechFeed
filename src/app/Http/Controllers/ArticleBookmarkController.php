<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Services\BookmarkService;

class ArticleBookmarkController extends Controller
{

    public function __construct(
        private BookmarkService $bookmarkService
    ) {
    }
    public function store(Article $article)
    {
        try {
            $user = auth()->user();
            $this->bookmarkService->bookmark($user, $article->id);
            return response()->json([
                'message' => 'ok',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }
}
