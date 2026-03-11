<?php

namespace App\Services;
use App\Models\Article;
use App\Models\UserBookmark;
use App\Http\Resources\ArticleResource;

class HomeService
{
    public function getHome($user)
    {
        $popular = $this->getPopularArticles($user);
        $latest = $this->getNewArticles($user);
        $recommended = $this->getRecommendedArticles($user, $popular, $latest);

        return [
            'serverTime' => now()->toISOString(),
            'sections' => [
                [
                    'key' => config('home.sections.recommended.key'),
                    'title' => config('home.sections.recommended.title'),
                    'items' => $recommended,
                ],
                [
                    'key' => config('home.sections.popular.key'),
                    'title' => config('home.sections.popular.title'),
                    'items' => $popular,
                ],
                [
                    'key' => config('home.sections.new.key'),
                    'title' => config('home.sections.new.title'),
                    'items' => $latest,
                ],
            ],
        ];
    }

    private function getBaseArticleQuery($user)
    {
        return Article::with(['source', 'categories', 'bookmarks' => fn($q) => $q->where('user_id', $user?->id)]);
    }

    public function getRecommendedArticles($user, $popularArticles, $latest)
    {
        if (!$user) {
            return $this->getRecommendedForGuest($popularArticles, $latest);
        }

        return $this->getRecommendedForUser($user, $popularArticles, $latest);
    }

    public function getRecommendedForGuest($popularArticles, $latest)
    {
        $limit = config('home.sections.recommended.limit');
        $articles = $this->getBaseArticleQuery(null)
            ->whereNotIn('id', $popularArticles->pluck('id'))
            ->whereNotIn('id', $latest->pluck('id'))
            ->orderByDesc('source_like_count')
            ->limit($limit)
            ->get();

        return $this->formatArticleList($articles);
    }

    public function getRecommendedForUser($user, $popularArticles, $latest)
    {
        $excludedIds = $popularArticles->pluck('id')->merge($latest->pluck('id'));
        $limit = config('home.sections.recommended.limit');

        $categoryIds = UserBookmark::query()
            ->join('article_categories', 'user_bookmarks.article_id', '=', 'article_categories.article_id')
            ->where('user_bookmarks.user_id', $user->id)
            ->select('article_categories.category_id')
            ->groupBy('article_categories.category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit($limit)
            ->pluck('category_id');

        $articles = $this->getBaseArticleQuery($user)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->whereDoesntHave('bookmarks', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereNotIn('id', $excludedIds)
            ->orderByDesc('source_like_count')
            ->limit($limit)
            ->get();

        if ($articles->count() < $limit) {
            $fallbackExcludedIds = $excludedIds->merge($articles->pluck('id'));
            $fallback = $this->getBaseArticleQuery($user)
                ->whereNotIn('id', $fallbackExcludedIds)
                ->orderByDesc('published_at')
                ->limit($limit - $articles->count())
                ->get();

            $articles = $articles->merge($fallback);
        }

        return $this->formatArticleList($articles);
    }

    public function getPopularArticles($user)
    {
        $limit = config('home.sections.popular.limit');
        $articles = $this->getBaseArticleQuery($user)
            ->orderByDesc('source_like_count')
            ->limit($limit)
            ->get();

        return $this->formatArticleList($articles);
    }

    public function getNewArticles($user)
    {
        $limit = config('home.sections.new.limit');
        $articles = $this->getBaseArticleQuery($user)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return $this->formatArticleList($articles);
    }

    public function formatArticleList($articles)
    {
        return ArticleResource::collection($articles);
    }
}
