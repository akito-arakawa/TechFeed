<?php

namespace App\Services;
use App\Models\User;
use App\Models\ArticleView;

class ViewService
{
    private const ARTICLES_PER_PAGE = 9;
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

    public function getViews(User $user, int $page)
    {
        return $user->views()
            ->with(['source', 'categories', 'bookmarks' => fn($query) => $query->where('user_id', $user->id)])
            ->orderByDesc('article_views.last_viewed_at')
            ->paginate(self::ARTICLES_PER_PAGE, ['*'], 'page', $page);
    }
}
