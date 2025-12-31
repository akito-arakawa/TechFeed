<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Services\BookmarkService;
use App\Http\Requests\BookmarkIndexRequest;
use App\Http\Resources\HomeResource;
use Illuminate\Support\Facades\Log;

class MeBookmarkController extends Controller
{
    public function __construct(
        private BookmarkService $bookmarkService
    ) {
    }
    public function index(BookmarkIndexRequest $req)
    {
        try {
            $validated = $req->validated();
            $page = $validated['page'] ?? 1;
            $user = auth()->user();
            $page = $req->input('page', $page);
            $articles = $this->bookmarkService->getBookmark($user, $page);

            return response()->json(
                ['articles' => HomeResource::collection($articles)->response()->getData(true)],
            );
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }

}
