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
}