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
            $user = auth()->user();

            $articles = $this->bookmarkService->getBookmark($user, $req->page);

            return response()->json(
                ['articles' => HomeResource::collection($articles)->response()->getData(true)],
            );
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'サーバーエラーが発生しました。'], 500);
        }
    }

}
