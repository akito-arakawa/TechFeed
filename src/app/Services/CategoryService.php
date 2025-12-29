<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Models\Article;

class CategoryService
{
    private const ARTICLES_PER_PAGE = 6;
    public function getArticlesByCategory(Category $category, ?User $user, int $page)
    {
        return Article::query()
            ->with([
                'source',
                'categories',
                'bookmarks' => fn($q) => $q->where('user_id', $user?->id),
            ])
            ->whereHas(
                'categories',
                fn($q) =>
                $q->where('categories.id', $category->id)
            )
            ->orderByDesc('source_like_count')
            ->paginate(self::ARTICLES_PER_PAGE, ['*'], 'page', $page);
    }
}
