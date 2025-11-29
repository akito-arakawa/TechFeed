<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ArticleFetcher\ZennFetcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class FetchZenn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:zenn {type} {value?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Zenn articles (new, popular, tag)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ZennFetcher $zenn)
    {
        $type = $this->argument('type');
        $value = $this->argument('value');

        $new = 0;
        $updated = 0;

        switch ($type) {
            case 'new':
                [$new, $updated] = $zenn->fetchNew();
                break;
            case 'popular':
                $term = $value ?? 'weekly';
                [$new, $updated] = $zenn->fetchPopular($term);
                break;
            case 'tag':
                if (!$value) {
                    $this->error('tag name requirad');
                    return Command::FAILURE;
                }
                [$new, $updated] = $zenn->fetchByTag($value);
                break;
            default:
                $this->error('Unknown type: ' . $type);
                return Command::FAILURE;
        }
        $this->info("Fetched successfully: new={$new}, updated={$updated}");

        return Command::SUCCESS;
    }
}
