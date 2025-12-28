<?php

namespace App\Services;
use App\Models\Article;
use App\Models\UserBookmark;

class HomeService
{
    public function getHome($user): array
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
        $articles = Article::with(['source', 'categories'])
            ->whereNotIn('id', $popularArticles->pluck('id'))
            ->whereNotIn('id', $latest->pluck('id'))
            ->orderByDesc('source_like_count')
            ->limit(config('home.sections.recommended.limit'))
            ->get();

        return $this->formatArticleList($articles);
    }

    public function getRecommendedForUser($user, $popularArticles, $latest)
    {
        $categoryIds = UserBookmark::query()
            ->join('article_categories', 'user_bookmarks.article_id', '=', 'article_categories.article_id')
            ->where('user_bookmarks.user_id', $user->id)
            ->select('article_categories.category_id')
            ->groupBy('article_categories.category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(3)
            ->pluck('category_id');

        $articles = $this->getBaseArticleQuery($user)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->whereDoesntHave('bookmarks', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereNotIn('id', $popularArticles->pluck('id'))
            ->whereNotIn('id', $latest->pluck('id'))
            ->orderByDesc('source_like_count')
            ->limit(config('home.sections.recommended.limit'))
            ->get();

        if ($articles->count() < 3) {
            $fallback = $this->getBaseArticleQuery($user)
                ->whereNotIn('id', $articles->pluck('id'))
                ->whereNotIn('id', $popularArticles->pluck('id'))
                ->whereNotIn('id', $latest->pluck('id'))
                ->orderByDesc('published_at')
                ->limit(3 - $articles->count())
                ->get();

            $articles = $articles->merge($fallback);
        }

        return $this->formatArticleList($articles);
    }


    public function getPopularArticles($user)
    {
        $articles = $this->getBaseArticleQuery($user)
            ->orderByDesc('source_like_count')
            ->limit(config('home.sections.popular.limit'))
            ->get();

        return $this->formatArticleList($articles);
    }

    public function getNewArticles($user)
    {
        $articles = $this->getBaseArticleQuery($user)
            ->orderByDesc('published_at')
            ->limit(config('home.sections.new.limit'))
            ->get();

        return $this->formatArticleList($articles);
    }

    public function formatArticleList($articles)
    {
        return $articles->map(function ($article) {

            return [
                'id' => $article->id,
                'title' => $article->title,
                'source' => [
                    'id' => $article->source->id,
                    'name' => $article->source->name,
                ],
                'thumbnailUrl' => $article->thumbnail_url,
                'categories' => $article->categories->map(fn($c) => [
                    'name' => $c->name,
                ]),
                'likeCount' => $article->source_like_count,
                'bookmarked' => $article->bookmarks->isNotEmpty(),
                'publishedAt' => $article->published_at->toISOString(),
            ];
        });
    }
}