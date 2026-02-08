<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MovieDetail;
use Illuminate\Console\Command;

final class SetMovieAffiliateLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movie:affiliate {imdbid} {title} {link?}';

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

        $movie = MovieDetail::where('imdb_id', $imdbId)->first();

        if (!$movie) {
            $this->error(sprintf('Movie with IMDB ID %s not found.', $imdbId));

            return 1;
        }

        if (!$link) {
            $link = 'https://www.google.com/search?q=' . urlencode($title . ' movie tickets ' . $movie->title);
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
