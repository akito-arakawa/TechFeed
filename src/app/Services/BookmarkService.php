<?php

namespace App\Services;
use App\Models\UserBookmark;
use App\Models\User;
class BookmarkService
{

    public function bookmark(User $user, int $articleId)
    {
        UserBookmark::firstOrCreate([
            'user_id' => $user->id,
            'article_id' => $articleId,
        ]);
    }

    public function unbookmark(User $user, int $articleId)
    {
        $deletedCount = UserBookmark::where('user_id', $user->id)
            ->where('article_id', $articleId)
            ->delete();

        if ($deletedCount === 0) {
            throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(UserBookmark::class);
        }
    }

}
