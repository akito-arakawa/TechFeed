<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\BookmarkService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

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
            Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }

    public function destroy(Article $article)
    {
        try {
            $user = auth()->user();
            $this->bookmarkService->unbookmark($user, $article->id);
            return response()->json([
                'message' => 'ok',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->noContent(); // modelがない場合でも問題なく動作させる
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }
}
