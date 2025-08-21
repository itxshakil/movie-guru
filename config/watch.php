<?php

declare(strict_types=1);

return [
    // Default region for watch providers queries
    'region' => env('WATCH_REGION', 'US'),

    // Days until cached where-to-watch data is considered expired
    'ttl_days' => env('WATCH_TTL_DAYS', 3),
];
