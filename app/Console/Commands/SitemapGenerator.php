<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Search;
use App\Models\ShowPageAnalytics;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapGenerator extends Command
{
    protected $signature = 'sitemap:generate {--limit=100}';

    protected $description = 'Generate the sitemap.';

    public function handle(): void
    {
        $sitemap = Sitemap::create();

        $sitemap->add(Url::create('/'))
            ->add(Url::create('/privacy'))
            ->add(Url::create('/terms'))
            ->add(Url::create('/contact'));

        $limit = $this->option('limit');
        $topQueries = $this->getTopSearches((int)$limit);
        foreach ($topQueries as $searchQuery) {
            $sitemap->add(Url::create("/search/$searchQuery->query"));
        }

        $topMovies = $this->getTopMovies((int)$limit);
        foreach ($topMovies as $topMovie) {
            $sitemap->add(Url::create("/i/$topMovie->imdb_id"));
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap has been generated!');
    }

    private function getTopSearches(int $limit): Collection
    {
        return Search::select('query')
            ->where('total_results', '>', 0)
            ->orderBy('search_count', 'desc')
            ->take($limit)
            ->get();
    }

    private function getTopMovies(int $limit): Collection
    {
        return ShowPageAnalytics::select('imdb_id', DB::raw('count(imdb_id) as total'))
            ->groupBy('imdb_id')
            ->orderBy('total', 'desc')
            ->take($limit)
            ->get();
    }
}
