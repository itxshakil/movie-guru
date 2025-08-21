<?php

namespace App\Jobs;

use App\Models\MovieDetail;
use App\Services\WatchProvidersService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateWhereToWatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly string $imdbId, private readonly string $region = 'US')
    {
    }

    public function handle(WatchProvidersService $providers): void
    {
        $ttlDays = (int) config('watch.ttl_days', 3);
        $movie = MovieDetail::where('imdb_id', $this->imdbId)->first();
        if (!$movie) {
            return; // Nothing to update yet
        }

        $type = strtolower((string) ($movie->type ?? ''));
        $tmdbType = $type === 'series' ? 'tv' : ($type === 'movie' ? 'movie' : null);

        $data = $providers->fetch($this->imdbId, $tmdbType, $this->region);
        $now = now();

        $movie->update([
            'where_to_watch' => $data,
            'where_to_watch_fetched_at' => $now,
            'where_to_watch_expires_at' => $now->copy()->addDays($ttlDays),
        ]);
    }
}
