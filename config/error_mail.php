<?php

declare(strict_types=1);

return [
    'enabled' => env('ERROR_MAIL_ENABLED', true),

    'recipients' => explode(',', (string)env('ERROR_MAIL_RECIPIENTS', '')),

    'rate_limit_minutes' => env('ERROR_MAIL_RATE_LIMIT', 5),

    'group_errors' => env('ERROR_MAIL_GROUP', true),

    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_key',
        'authorization',
    ],
];
