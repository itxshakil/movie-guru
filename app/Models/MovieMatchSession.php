<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class MovieMatchSession extends Model
{
    protected $fillable = [
        'token',
        'creator_picks',
        'partner_picks',
        'matched',
        'expires_at',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    protected function casts(): array
    {
        return [
            'creator_picks' => 'array',
            'partner_picks' => 'array',
            'matched' => 'array',
            'expires_at' => 'datetime',
        ];
    }
}
