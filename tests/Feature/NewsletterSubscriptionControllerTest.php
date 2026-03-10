<?php

declare(strict_types=1);

use App\Mail\WelcomeSubscriberMail;
use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('subscribes a new user and sends welcome email', function (): void {
    Mail::fake();

    $this->post(route('subscribe'), [
        'email' => 'newuser@example.com',
        'first_name' => 'Alice',
    ])->assertRedirect();

    expect(NewsletterSubscription::where('email', 'newuser@example.com')->exists())->toBeTrue();

    Mail::assertQueued(WelcomeSubscriberMail::class, function (WelcomeSubscriberMail $mail): bool {
        return $mail->email === 'newuser@example.com' && $mail->firstName === 'Alice';
    });
});

it('subscribes without first name and uses default in welcome email', function (): void {
    Mail::fake();

    $this->post(route('subscribe'), [
        'email' => 'noname@example.com',
    ])->assertRedirect();

    Mail::assertQueued(WelcomeSubscriberMail::class, function (WelcomeSubscriberMail $mail): bool {
        return $mail->firstName === 'Movie Fan';
    });
});

it('does not send welcome email when re-subscribing', function (): void {
    Mail::fake();

    NewsletterSubscription::factory()->create(['email' => 'existing@example.com']);

    $this->post(route('subscribe'), [
        'email' => 'existing@example.com',
    ])->assertRedirect();

    Mail::assertNotQueued(WelcomeSubscriberMail::class);
});

it('rejects invalid email', function (): void {
    $this->post(route('subscribe'), [
        'email' => 'not-an-email',
    ])->assertSessionHasErrors('email');
});

it('rejects first name that is too long', function (): void {
    $this->post(route('subscribe'), [
        'email' => 'valid@example.com',
        'first_name' => str_repeat('a', 51),
    ])->assertSessionHasErrors('first_name');
});
