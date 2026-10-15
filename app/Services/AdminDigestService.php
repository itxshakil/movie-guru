<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\AdminDigestData;
use App\Enums\LibraryStatus;
use App\Models\Contact;
use App\Models\LibraryItem;
use App\Models\MovieDetail;
use App\Models\NewsletterSubscription;
use App\Models\Search;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use SplFileObject;

final class AdminDigestService
{
    public function generate(CarbonImmutable $start, CarbonImmutable $end, string $type): AdminDigestData
    {
        $newSignups = User::query()
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(10)
            ->get(['name', 'email']);

        return new AdminDigestData(
            type: $type,
            periodStart: $start,
            periodEnd: $end,
            newSignupsCount: User::query()->whereBetween('created_at', [$start, $end])->count(),
            newSignups: $newSignups->map(static fn (User $user): array => [
                'name' => $user->name,
                'email' => $user->email,
            ])->all(),
            newsletterSubscribesCount: NewsletterSubscription::withTrashed()
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            newsletterUnsubscribesCount: NewsletterSubscription::onlyTrashed()
                ->whereBetween('unsubscribed_at', [$start, $end])
                ->count(),
            contactSubmissionsCount: Contact::query()->whereBetween('created_at', [$start, $end])->count(),
            watchlistAddsCount: LibraryItem::query()->where('status', LibraryStatus::ToWatch)->whereBetween('created_at', [$start, $end])->count(),
            newMoviesCount: MovieDetail::query()->whereBetween('created_at', [$start, $end])->count(),
            errorLogCount: $this->countErrorLogLines($start, $end),
            topSearches: $type === 'monthly' ? $this->getTopSearches() : [],
        );
    }

    /**
     * @return array<int, array{query: string, count: int}>
     */
    private function getTopSearches(): array
    {
        return Search::popular()
            ->limit(10)
            ->get(['query', 'search_count'])
            ->map(static fn (Search $search): array => [
                'query' => $search->query,
                'count' => $search->search_count,
            ])
            ->all();
    }

    private function countErrorLogLines(CarbonImmutable $start, CarbonImmutable $end): int
    {
        $path = config('logging.channels.single.path', storage_path('logs/app.log'));

        if (! is_string($path) || ! is_readable($path)) {
            return 0;
        }

        $count = 0;
        $file = new SplFileObject($path, 'r');

        while (! $file->eof()) {
            $line = $file->fgets();

            if ($line === false) {
                continue;
            }

            // Only record-start lines (those beginning with a timestamp) are counted;
            // stack-trace continuation lines are skipped even if they mention ".ERROR:".
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches) !== 1) {
                continue;
            }

            $timestamp = Date::createFromFormat('Y-m-d H:i:s', $matches[1], config('app.timezone'));

            if ($timestamp instanceof Carbon && $timestamp->betweenIncluded($start, $end) && str_contains($line, '.ERROR:')) {
                $count++;
            }
        }

        return $count;
    }
}
