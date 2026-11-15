<?php

declare(strict_types=1);

return [
    'api_keys' => env('OMDB_API_KEYS'),

    /*
    | How many OMDb calls crawlers and bots may cause per day, across the whole site.
    | Past it they only get what is already cached or stored, so bots cannot drain the API quota.
    */
    'bot_daily_budget' => (int) env('OMDB_BOT_DAILY_BUDGET', 100),
];
