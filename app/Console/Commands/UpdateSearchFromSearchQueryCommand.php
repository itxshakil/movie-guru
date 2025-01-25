<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Search;
use App\Support\LogCommands;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSearchFromSearchQueryCommand extends Command
{
    use LogCommands;

    protected $signature = 'search:update-counts';

    protected $description = 'Update the Search model with counts of search queries from the search_queries table';

    public function handle(): void
    {
        $this->log('Starting the update of search counts.');

        try {
            // Retrieve search queries with their count
            $queryCounts = DB::table('search_queries')
                ->select('query', DB::raw('count(*) as count'))
                ->groupBy('query')
                ->get();

            foreach ($queryCounts as $queryCount) {
                Search::updateOrCreate(
                    ['query' => $queryCount->query],
                    [
                        'search_count' => $queryCount->count,
                        'total_results' => 1
                    ]
                );
            }

            $this->log('Search model updated successfully from search queries.');
        } catch (Exception $e) {
            $this->logException($e);
        }
    }
}
