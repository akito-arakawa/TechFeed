<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
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
}
