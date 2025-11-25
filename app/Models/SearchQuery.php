<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'page',
        'type',
        'year',
        'ip_address',
        'user_agent',
        'response_at',
        'response',
        'response_result',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_at' => 'datetime',
            'response' => 'boolean',
            'response_result' => 'json',
        ];
    }
}
