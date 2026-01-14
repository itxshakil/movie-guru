<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class NewsletterSubscription extends Model
{
    /**
     * @use HasFactory<self>
     */
    use HasFactory;
    use SoftDeletes;

    public const string DELETED_AT = 'unsubscribed_at';

    protected $fillable = [
        'email',
        'unsubscribed_at',
        'first_name',
        'last_name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'timestamp',
        ];
    }
}
