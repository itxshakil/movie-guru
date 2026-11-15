<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Log;

/**
 * The site-wide daily allowance of OMDb calls that crawlers and bots may cause.
 */
final readonly class BotFetchBudget
{
    private const string KEY = 'omdb-bot-budget';

    private const int WINDOW_SECONDS = 86_400;

    public function __construct(
        private RateLimiter $rateLimiter,
        #[Config('omdb.bot_daily_budget')]
        private int $dailyLimit,
    ) {}

    /**
     * Take one call from today's allowance, or return false when it is used up.
     */
    public function spend(): bool
    {
        if ($this->isDisabled() || $this->rateLimiter->tooManyAttempts(self::KEY, $this->dailyLimit)) {
            return false;
        }

        $attempts = $this->rateLimiter->hit(self::KEY, self::WINDOW_SECONDS);

        if ($attempts === $this->dailyLimit) {
            Log::warning('Bots used up today\'s OMDb budget; they get cached and stored data only until it refills.', [
                'daily_limit' => $this->dailyLimit,
            ]);
        }

        return true;
    }

    public function secondsUntilRefill(): int
    {
        if ($this->isDisabled()) {
            return self::WINDOW_SECONDS;
        }

        return max(1, $this->rateLimiter->availableIn(self::KEY));
    }

    /**
     * A budget of zero means bots never cause an OMDb call.
     */
    private function isDisabled(): bool
    {
        return $this->dailyLimit <= 0;
    }
}
