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
    protected $signature = 'fetch:qitta {type} {value?}';

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
        $type = $this->argument('type');
        $value = $this->argument('value');

        switch ($type) {
            case 'new':
                [$new, $updated] = $qiita->fetchNew();
                break;
            case 'popular':
                [$new, $updated] = $qiita->fetchPopular();
                break;
            case 'tag':
                if (!$value) {
                    $this->error('tag name requirad');
                    return Command::FAILURE;
                }
                [$new, $updated] = $qiita->fetchByTag($value);
                break;

            default:
                $this->error('Unknown target: ' . $type);
                return Command::FAILURE;
        }

        $this->info("Fetched successfully: new={$new}, updated={$updated}");
        return Command::SUCCESS;
    }
}
