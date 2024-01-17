<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowPageAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'imdb_id',
        'ip_address',
        'user_agent',
    ];
}
