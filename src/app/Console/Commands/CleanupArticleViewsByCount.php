<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArticleView;

class CleanupArticleViewsByCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'article-views:cleanup-by-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limit article views per user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $maxViewsPerUser = config('article_views.max_per_user');
        $limit = config('article_views.limit');
        
        $userIds = ArticleView::select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > ?'[$maxViewsPerUser])
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $keepIds = ArticleView::where('user_id', $userId)
                ->orderByDesc('last_viewed_at')
                ->limit($limit)
                ->pluck('id');

            ArticleView::where('user_id', $userId)
                ->whereNotIn('id', $keepIds)
                ->delete();
        }
    }
}
