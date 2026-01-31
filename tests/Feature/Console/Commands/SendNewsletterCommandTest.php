<?php

declare(strict_types=1);

use App\Mail\NewsletterMail;
use App\Models\MovieDetail;
use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('sends weekly newsletter to subscribers', function () {
    Mail::fake();

    NewsletterSubscription::factory()->create(['email' => 'test@example.com']);
    MovieDetail::factory()->count(5)->create(['imdb_rating' => 9.0, 'imdb_votes' => 100000, 'year' => now()->year]);

    // Create recommended movie
    MovieDetail::factory()->create([
        'imdb_rating' => 7.2,
        'imdb_votes' => 60000,
    ]);

    // Create hidden gem
    MovieDetail::factory()->create([
        'imdb_rating' => 8.6,
        'imdb_votes' => 10000,
    ]);

    $this->artisan('newsletter:send', ['type' => 'weekly'])
        ->assertExitCode(0);

    Mail::assertQueued(NewsletterMail::class, function ($mail) {
        return $mail->recommendedMovie !== null && $mail->hiddenGem !== null && $mail->unsubscribeUrl !== null;
    });
});

it('sends monthly newsletter to subscribers', function () {
    Mail::fake();

    NewsletterSubscription::factory()->create(['email' => 'test@example.com']);
    MovieDetail::factory()->count(5)->create(['year' => now()->year]);

    $this->artisan('newsletter:send', ['type' => 'monthly'])
        ->assertExitCode(0);

    Mail::assertQueued(NewsletterMail::class);
});

it('does not send newsletter if no movies found', function () {
    Mail::fake();

    NewsletterSubscription::factory()->create(['email' => 'test@example.com']);
    // No movies created

    $this->artisan('newsletter:send weekly')
        ->expectsOutput('No movies found to include in the weekly newsletter.');

    Mail::assertNotSent(NewsletterMail::class);
});
