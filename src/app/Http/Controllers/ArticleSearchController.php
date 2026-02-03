<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\ArticleSearchService;
use App\Http\Resources\ArticleResource;
use Illuminate\Support\Facades\Log;


class ArticleSearchController extends Controller
{
    public function __construct(
        private ArticleSearchService $articleSearchService
    ) {
    }

    public function index(SearchRequest $req)
    {
        try {
            $defaultPage = 1;
            $validated = $req->validated();
            $keyword = $validated['keyword'] ?? '';
            $category = $validated['category'] ?? 'all';
            $sort = $validated['sort'] ?? 'postDate';
            $user = auth()->user();
            $page = $validated['page'] ?? $defaultPage;

            $articles = $this->articleSearchService->articleSearch($keyword, $category, $sort, $user, $page);
            
            return response()->json([
                'articles' => ArticleResource::collection($articles)->response()->getData(true),
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }

    }
}
