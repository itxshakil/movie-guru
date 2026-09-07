<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\CarbonImmutable;

final readonly class AdminDigestData
{
    /**
     * @param array<int, array{name: string, email: string}> $newSignups
     * @param array<int, array{query: string, count: int}>   $topSearches
     */
    public function __construct(
        public string $type,
        public CarbonImmutable $periodStart,
        public CarbonImmutable $periodEnd,
        public int $newSignupsCount,
        public array $newSignups,
        public int $newsletterSubscribesCount,
        public int $newsletterUnsubscribesCount,
        public int $contactSubmissionsCount,
        public int $watchlistAddsCount,
        public int $newMoviesCount,
        public int $errorLogCount,
        public array $topSearches = [],
    ) {}
}
