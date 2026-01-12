<?php

namespace App\Services;
use App\Models\Article;
use App\Models\User;

class ArticleSearchService
{
    private const ARTICLES_PER_PAGE = 12;
    public function articleSearch(string $keyword, string $category, string $sort, User $user, int $page)
    {
        $query = Article::with([
            'source',
            'categories',
            'bookmarks' => fn($q) => $q->where('user_id', $user?->id),
        ]);

        if (filled($keyword)) {
            $query->where('title', 'like', "%{$keyword}%");
        }

        if ($category !== "all") {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        if ($sort === 'like') {
            $query->orderBy('source_like_count', 'desc');
        } else {
            $query->orderBy('published_at', 'desc');
        }

        return $query->paginate(self::ARTICLES_PER_PAGE, ['*'], 'page', $page);
    }
}