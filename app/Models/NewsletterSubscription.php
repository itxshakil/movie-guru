<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsletterSubscription extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'email',
        'unsubscribed_at',
        'first_name',
        'last_name',
    ];

    protected $casts = [
        'unsubscribed_at' => 'timestamp',
    ];

    public const DELETED_AT = 'unsubscribed_at';
}
