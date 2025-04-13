<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Log;

class Search extends Model
{
    protected $fillable = [
        'query',
        'total_results',
        'search_count',
    ];

    protected $casts = [
        'search_count' => 'integer',
        'total_results' => 'integer',
    ];

    public function incrementViews(): void
    {
        $browser = request()->userAgent();
        $ipAddress = request()->ip();

        $botPatterns = [
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
        ];

        foreach ($botPatterns as $pattern) {
            if (preg_match('/'.$pattern.'/i', $browser)) {
                Log::info('Bot detected',
                    [
                        'ip' => $ipAddress,
                        'user_agent' => $browser,
                        'query' => $this->query,
                        'reason' => 'User agent matched bot pattern: '.$pattern
                    ]
                );

                return;
            }
        }

        $cacheKey = 'search-query-'.$this->query.'-'.$ipAddress;

        if (!Cache::has($cacheKey)) {
            $this->increment('search_count');
            Cache::put($cacheKey, true, now()->addHour());
        }
    }

    public function scopeHasResults($query): void
    {
        $query->where('total_results', '>', 0);
    }

    public function scopeRecentOnly(Builder $query, int $days = 28): void
    {
        $query->where('updated_at', '>', now()->subDays($days));
    }

    public function scopePopular(Builder $query): void
    {
        $query->orderByDesc('total_results');
    }
}
