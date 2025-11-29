<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\ArticleFetcher\QiitaFetcher;

class FetchQitta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:fetch {target}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from Qiita';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(QiitaFetcher $qiita)
    {
        $target = $this->argument('target');

        switch ($target) {
            case 'qiita:new':
                [$new, $updated, $skipped] = $qiita->fetchNew();
                break;
            case 'qiita:popular':
                [$new, $updated, $skipped] = $qiita->fetchPopular();
                break;

            case (str_starts_with($target, 'qiita:tag:')):
                $tag = str_replace('qiita:tag:', '', $target);
                [$new, $updated, $skipped] = $qiita->fetchByTag($tag);
                break;

            default:
                $this->error('Unknown target: ' . $target);
                return Command::FAILURE;
        }

        $this->info("Fetched successfully: new={$new}, updated={$updated}, skipped={$skipped}");
        return Command::SUCCESS;
    }
}
