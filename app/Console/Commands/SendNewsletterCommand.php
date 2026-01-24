<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\NewsletterMail;
use App\Models\MovieDetail;
use App\Models\NewsletterSubscription;
use App\Support\LogCommands;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

final class SendNewsletterCommand extends Command
{
    use LogCommands;

    protected $signature = 'newsletter:send {type : The type of newsletter (weekly or monthly)}';

    protected $description = 'Send weekly or monthly newsletters to subscribers';

    public function handle(): void
    {
        $type = (string)$this->argument('type');

        if (!in_array($type, ['weekly', 'monthly'])) {
            $this->logError('Invalid newsletter type. Use "weekly" or "monthly".');

            return;
        }

        $this->log("Starting $type newsletter delivery.");

        $movies = $this->getMoviesForNewsletter($type);
        $recommendedMovie = MovieDetail::recommended()->inRandomOrder()->first();
        $hiddenGem = MovieDetail::hiddenGems()->inRandomOrder()->first();
        $trendingMovie = MovieDetail::trending()->inRandomOrder()->first();

        if ($movies->isEmpty()) {
            $this->warning("No movies found to include in the $type newsletter.");

            return;
        }

        $subscribers = NewsletterSubscription::query()->get();

        if ($subscribers->isEmpty()) {
            $this->log('No subscribers found.');

            return;
        }

        foreach ($subscribers as $subscriber) {
            $unsubscribeUrl = URL::temporarySignedRoute(
                'unsubscribe',
                now()->addWeeks(2),
                ['email' => $subscriber->email],
            );

            Mail::to($subscriber->email)->queue(new NewsletterMail(
                $type,
                $movies,
                $subscriber->email,
                $recommendedMovie,
                $hiddenGem,
                $trendingMovie,
                $unsubscribeUrl,
            ));
        }

        $this->log("Finished sending $type newsletter to {$subscribers->count()} subscribers.");
    }

    /**
     * @return Collection<int, MovieDetail>
     */
    private function getMoviesForNewsletter(string $type): Collection
    {
        if ($type === 'weekly') {
            return MovieDetail::recentlyReleased()->limit(5)->get();
        }

        return MovieDetail::recentlyReleased()->limit(10)->get();
    }
}
