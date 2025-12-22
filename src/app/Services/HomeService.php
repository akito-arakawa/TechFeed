<?php

namespace App\Services;
use App\Models\Article;
use App\Models\UserLike;

class HomeService
{
    public function getHome($user): array
    {
        $popular = $this->getPopularArticles();
        $recommended = $this->getRecommendedArticles($user, $popular);
        $latest = $this->getNewArticles();

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

    public function getRecommendedArticles($user, $popularArticles)
    {
        if (!$user) {
            return $this->getRecommendedForGuest($popularArticles);
        }

        return $this->getRecommendedForUser($user);
    }

    public function getRecommendedForGuest($popularArticles)
    {
        return Article::whereNotIn('id', $popularArticles->pluck('id'))
            ->orderByDesc('source_like_count')
            ->limit(config('home.sections.recommended.limit'))
            ->get();
    }

    public function getRecommendedForUser($user)
    {
        $categoryIds = UserLike::query()
            ->join('article_categories', 'user_likes.article_id', '=', 'article_categories.article_id')
            ->where('user_likes.user_id', $user->id)
            ->select('article_categories.category_id')
            ->groupBy('article_categories.category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(3)
            ->pluck('category_id');

        $articles = Article::query()
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->whereDoesntHave('likes', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('source_like_count')
            ->limit(config('home.sections.recommended.limit'))
            ->get();

        if ($articles->count() < 3) {
            $articles = $articles->merge(
                Article::query()
                    ->whereNotIn('id', $articles->pluck('id'))
                    ->orderByDesc('published_at')
                    ->limit(3 - $articles->count())
                    ->get()
            );
        }

        return $articles;
    }

    public function getPopularArticles()
    {
        return Article::query()
            ->orderByDesc('source_like_count')
            ->limit(config('home.sections.popular.limit'))
            ->get();
    }

    public function getNewArticles()
    {
        return Article::query()
            ->orderByDesc('published_at')
            ->limit(config('home.sections.popular.limit'))
            ->get();
    }

}