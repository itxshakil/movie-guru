<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MovieDetail;
use Illuminate\Console\Command;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;

final class SetMovieAffiliateLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movie:affiliate {imdbid?} {title?} {link?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the movie booking affiliate link for a given IMDB ID';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $imdbId = $this->argument('imdbid');
        $title = $this->argument('title');
        $link = $this->argument('link');

        if (!$imdbId) {
            $imdbId = search(
                label: 'Search for a movie by title',
                options: function (string $query): array {
                    if (mb_strlen($query) < 2) {
                        return [];
                    }

                    return MovieDetail::query()
                        ->where('title', 'like', '%' . $query . '%')
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(fn(MovieDetail $movie): array => [
                            $movie->imdb_id => sprintf('%s (%s) [%s]', $movie->title, $movie->year, $movie->type),
                        ])
                        ->all();
                },
                placeholder: 'Type at least 2 characters to search...',
            );
        }

        $movie = MovieDetail::where('imdb_id', $imdbId)->first();

        if (!$movie) {
            $this->error(sprintf('Movie with IMDB ID %s not found.', $imdbId));

            return 1;
        }

        if (!$title) {
            $title = text(
                label: 'Enter the affiliate title label',
                default: $movie->title,
                required: true,
            );
        }

        if (!$link) {
            $link = text(
                label: 'Enter the affiliate link',
                default: 'https://www.google.com/search?q=' . urlencode($title . ' movie tickets ' . $movie->title),
                required: true,
            );
        }

        /** @var array<string, string> $affiliateLink */
        $affiliateLink = [
            'link' => $link,
            'title' => $title,
        ];

        /** @phpstan-ignore-next-line */
        $movie->affiliate_link = $affiliateLink;

        $movie->save();

        $this->info(sprintf("Affiliate link for movie '%s' set to '%s' (%s).", $movie->title, $title, $link));

        return 0;
    }
}
