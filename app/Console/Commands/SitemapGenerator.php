<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\SearchQuery;
use App\Models\ShowPageAnalytics;
use Illuminate\Support\Facades\DB;

class SitemapGenerator extends Command
{
    protected $signature = 'sitemap:generate {--limit=10}';

    protected $description = 'Generate the sitemap.';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Add static pages
        $sitemap->add(Url::create('/home'))
                ->add(Url::create('/about'))
                ->add(Url::create('/contact'));

        // Add top queries
        $limit = $this->option('limit');
        $topQueries = $this->getTopSearches($limit);
        foreach ($topQueries as $searchQuery) {
            $sitemap->add(Url::create("/search?s={$searchQuery->query}"));
        }

        // Add top movies
        $topMovies = $this->getTopMovies($limit);
        foreach ($topMovies as $topMovie) {
            $sitemap->add(Url::create("/i/{$topMovie->imdb_id}"));
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap has been generated!');
    }

    private function getTopSearches(int $limit)
    {
        return SearchQuery::select('query', DB::raw('count(query) as total'))
            ->groupBy('query')
            ->orderBy('total', 'desc')
            ->take($limit)
            ->get();
    }

    private function getTopMovies(int $limit)
    {
        return ShowPageAnalytics::select('imdb_id', DB::raw('count(imdb_id) as total'))
            ->groupBy('imdb_id')
            ->orderBy('total', 'desc')
            ->take($limit)
            ->get();
    }
}
