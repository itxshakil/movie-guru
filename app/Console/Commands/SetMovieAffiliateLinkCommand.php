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
    public function handle()
    {
        $imdbId = $this->argument('imdbid');
        $title = $this->argument('title');
        $link = $this->argument('link');

        $movie = MovieDetail::where('imdb_id', $imdbId)->first();

        if (!$movie) {
            $this->error("Movie with IMDB ID {$imdbId} not found.");

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

        $this->info("Affiliate link for movie '{$movie->title}' set to '{$title}' ({$link}).");

        return 0;
    }
}
