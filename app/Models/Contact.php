<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\ContactFormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Override;

final class Contact extends Model
{
    protected $fillable = ['email', 'name', 'message'];

    #[Override]
    protected static function booted(): void
    {
        parent::booted();

        self::created(static function (Contact $contact): void {
            Notification::route('mail', config('mail.admin.address'))
                ->notify(new ContactFormSubmission($contact));
        });
    }
}
