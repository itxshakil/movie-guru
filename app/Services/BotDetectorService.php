<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class BotDetectorService
{
    /**
     * List of common bot patterns to detect in user agents.
     *
     * @var string[]
     */
    private array $botPatterns = [
        'bot',
        'crawler',
        'spider',
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'yandexbot',
        'baiduspider',
        'facebot',
        'ia_archiver',
        'magpie-crawler',
        'mediapartners-google',
        'msnbot',
        'pinterestbot',
        'redditbot',
        'seokicks-robot',
        'semrushbot',
        'twitterbot',
        'whatsapp',
        'yahoo! slurp',
        'zabbix',
        'uptimerobot',
        'datadog',
        'statuscake',
        'cloudflare',
        'netcraft',
        'ahrefsbot',
        'mj12bot',
        'blexbot',
        'heritrix',
        'headless',
        'phantom',
        'selenium',
        'puppeteer',
        'chrome-lighthouse',
    ];

    /**
     * IP address ranges known to be associated with bots.
     *
     * @var string[]
     */
    private array $knownBotIpRanges = [
        // Google bot IP ranges
        '66.249.64.0/19',
        '64.233.160.0/19',
        // Bing bot IP ranges
        '157.55.39.0/24',
        '207.46.13.0/24',
        // Add more ranges as needed
    ];

    /**
     * Cache duration for bot detection results in minutes.
     */
    private int $cacheDuration = 60;

    /**
     * Determine if the request is from a bot.
     */
    public function isBot(Request $request): bool
    {
        $browser = $request->userAgent();
        $ipAddress = $request->ip();

        // Generate a cache key based on IP and user agent
        $cacheKey = 'bot_detection_' . md5($ipAddress . $browser);

        // Return cached result if available
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $isBot =
            $this->checkUserAgent($browser, $ipAddress, $request)
            || $this->checkIpRange($ipAddress)
            || $this->checkBehavioralPatterns($request);

        // Cache the result
        Cache::put($cacheKey, $isBot, $this->cacheDuration * 60);

        return $isBot;
    }

    /**
     * Check if user agent matches any bot patterns.
     */
    private function checkUserAgent(string $userAgent, string $ipAddress, Request $request): bool
    {
        if ($userAgent === '' || $userAgent === '0') {
            $this->logBotDetection($ipAddress, $userAgent, $request, 'Empty user agent');

            return true;
        }

        foreach ($this->botPatterns as $botPattern) {
            if (mb_stripos($userAgent, (string)$botPattern) === false) {
                continue;
            }

            $this->logBotDetection($ipAddress, $userAgent, $request, 'User agent matched bot pattern: ' . $botPattern);

            return true;
        }

        return false;
    }

    /**
     * Log bot detection information.
     */
    private function logBotDetection(string $ipAddress, string $userAgent, ?Request $request, string $reason): void
    {
        $logData = [
            'ip' => $ipAddress,
            'user_agent' => $userAgent,
            'reason' => $reason,
        ];

        if ($request instanceof Request) {
            $logData['path'] = $request->path();
            $logData['query'] = $request->query();
            $logData['method'] = $request->method();
            $logData['referer'] = $request->header('referer');
        }

        Log::info('Bot detected', $logData);
    }

    /**
     * Check if IP is in a known bot IP range.
     */
    private function checkIpRange(string $ipAddress): bool
    {
        if ($ipAddress === '' || $ipAddress === '0') {
            return false;
        }

        foreach ($this->knownBotIpRanges as $knownBotIpRange) {
            if (!$this->ipInRange($ipAddress, $knownBotIpRange)) {
                continue;
            }

            $this->logBotDetection($ipAddress, '', null, 'IP in known bot range: ' . $knownBotIpRange);

            return true;
        }

        return false;
    }

    /**
     * Check if an IP address is within a CIDR range.
     *
     * @param string $range CIDR format range
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $bits = (int)$bits;
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }

    /**
     * Check if the request exhibits behavioral patterns of a bot.
     */
    private function checkBehavioralPatterns(Request $request): bool
    {
        // Check request headers that are commonly missing in bots
        if (!$request->header('Accept-Language') && !$request->header('Accept-Encoding')) {
            $this->logBotDetection($request->ip(), $request->userAgent(), $request, 'Missing critical headers');

            return true;
        }

        // Add additional behavioral checks as needed

        return false;
    }
}
