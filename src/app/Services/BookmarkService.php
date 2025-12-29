<?php

namespace App\Services;
use App\Models\UserBookmark;
use App\Models\User;
class BookmarkService
{

    public function bookmark(User $user, int $articleId)
    {
        $exists = UserBookmark::where('user_id', $user->id)
            ->where('article_id', $articleId)
            ->exists();

        if ($exists) {
            return;
        }

        UserBookmark::create([
            'user_id' => $user->id,
            'article_id' => $articleId,
            'create_at' => now(),
        ]);
    }
}