<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
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

    protected $casts = [
        'response_at' => 'datetime',
        'response' => 'boolean',
        'response_result' => 'json',
    ];
}
