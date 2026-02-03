<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;

class CategoryController extends Controller
{

    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    public function index()
    {
        try {
            return response()->json([
                'categories' => CategoryResource::collection(Category::all()),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }

    public function show(CategoryRequest $req)
    {
        $defaultPage = 1;

        try {
            $user = auth()->user();
            $category = Category::slug($req->slug)->firstOrFail();
            $page = $req->input('page', $defaultPage);

            $articles = $this->categoryService->getArticlesByCategory(
                $category,
                $user,
                $page,
            );

            return response()->json([
                'category' => CategoryResource::make($category),
                'articles' => ArticleResource::collection($articles)->response()->getData(true),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'カテゴリが見つかりません。'], 404);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }
}
