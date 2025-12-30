<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ArticleView;

class CleanupArticleViewsByDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'article-views:cleanup-by-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete article views older than 30 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $expireDays = config('article_views.expire_days');
        
        ArticleView::where('last_viewed_at', '<', now()->subDays($expireDays))
            ->delete();
    }
}
