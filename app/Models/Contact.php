<?php

namespace App\Models;

use App\Notifications\ContactFormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class Contact extends Model
{
    protected $fillable = ['email', 'name', 'message'];

    protected static function booted(): void
    {
        parent::booted();

        static::created(function (Contact $contact) {
            Notification::route('mail', config('mail.admin.address'))
                ->notify(new ContactFormSubmission($contact));
        });
    }
}
