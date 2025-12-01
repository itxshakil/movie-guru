<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MovieDetail;
use App\Models\Search;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

final class SitemapGenerator extends Command
{
    protected $signature = 'sitemap:generate {--limit=1000 : Number of top searches and movies to include}';

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
        foreach ($topQueries as $topQuery) {
            $sitemap->add(Url::create('/search/' . $topQuery->query));
        }

        $topMovies = $this->getTopMovies((int)$limit);
        foreach ($topMovies as $topMovie) {
            $sitemap->add(Url::create('/i/' . $topMovie->imdb_id));
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap has been generated!');
    }

    /**
     * @param int $limit
     * @return Collection<int, Search>
     */
    private function getTopSearches(int $limit): Collection
    {
        return Search::select('query')
            ->where('total_results', '>', 0)
            ->orderBy('search_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * @param int $limit
     * @return Collection<int, MovieDetail>
     */
    private function getTopMovies(int $limit): Collection
    {
        return MovieDetail::select('imdb_id', 'views')
            ->orderBy('views', 'desc')
            ->take($limit)
            ->get();
    }
}
