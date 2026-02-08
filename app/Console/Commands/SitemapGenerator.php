<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MovieDetail;
use App\Models\Search;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

final class SitemapGenerator extends Command
{
    protected $signature = 'sitemap:generate {--limit=1000 : Number of top searches and movies to include}';

    protected $description = 'Generate the sitemap.';

    public function handle(): void
    {
        $sitemap = Sitemap::create();

        $sitemap
            ->add(Url::create('/'))
            ->add(Url::create('/privacy'))
            ->add(Url::create('/terms'))
            ->add(Url::create('/contact'));

        $limit = (int)$this->option('limit');

        $this->getTopSearches($limit)->each(static function (Search $topQuery) use ($sitemap): void {
            $sitemap->add(Url::create('/search/' . $topQuery->query));
        });

        $this->getTopMovies($limit)->each(static function (MovieDetail $topMovie) use ($sitemap): void {
            $sitemap->add(Url::create('/movie/' . $topMovie->imdb_id));
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap has been generated!');
    }

    /**
     * @return LazyCollection<int, Search>
     */
    private function getTopSearches(int $limit): LazyCollection
    {
        return Search::select('query')
            ->where('total_results', '>', 0)
            ->orderBy('search_count', 'desc')
            ->take($limit)
            ->cursor();
    }

    /**
     * @return LazyCollection<int, MovieDetail>
     */
    private function getTopMovies(int $limit): LazyCollection
    {
        return MovieDetail::select('imdb_id', 'views')
            ->orderBy('views', 'desc')
            ->take($limit)
            ->cursor();
    }
}
