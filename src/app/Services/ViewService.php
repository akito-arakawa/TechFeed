<?php

namespace App\Services;
use App\Models\User;
use App\Models\ArticleView;

class ViewService
{
    public function viewHistory(User $user, int $articleId)
    {
        ArticleView::updateOrCreate(
            [
                'user_id' => $user->id,
                'article_id' => $articleId,
            ],
            [
                'last_viewed_at' => now(),
            ]
        );
    }
}
