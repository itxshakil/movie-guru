<?php

declare(strict_types=1);

use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('subscribes a new email', function (): void {
    $response = $this->post('/subscribe', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('newsletter_subscriptions', [
        'email' => 'test@example.com',
        'unsubscribed_at' => null,
    ]);
});

it('unsubscribes an email using a signed URL', function (): void {
    $subscription = NewsletterSubscription::factory()->create([
        'email' => 'test@example.com',
    ]);

    $url = URL::temporarySignedRoute(
        'unsubscribe',
        now()->addWeeks(2),
        ['email' => 'test@example.com'],
    );

    $response = $this->get($url);

    $response->assertRedirect('/');
    $this->assertDatabaseHas('newsletter_subscriptions', [
        'email' => 'test@example.com',
    ]);
    $this->assertNotNull(
        NewsletterSubscription::withTrashed()
            ->where('email', 'test@example.com')
            ->first()
            ->unsubscribed_at,
    );
});

it('fails to unsubscribe if signature is invalid', function (): void {
    $subscription = NewsletterSubscription::factory()->create([
        'email' => 'test@example.com',
    ]);

    $url = URL::temporarySignedRoute(
        'unsubscribe',
        now()->addWeeks(2),
        ['email' => 'test@example.com'],
    );

    $response = $this->get($url . 'tampered');

    $response->assertStatus(403);
    $this->assertDatabaseHas('newsletter_subscriptions', [
        'email' => 'test@example.com',
        'unsubscribed_at' => null,
    ]);
});

it('fails to unsubscribe if signed URL is expired', function (): void {
    $subscription = NewsletterSubscription::factory()->create([
        'email' => 'test@example.com',
    ]);

    $url = URL::temporarySignedRoute(
        'unsubscribe',
        now()->subSecond(),
        ['email' => 'test@example.com'],
    );

    $response = $this->get($url);

    $response->assertStatus(403);
    $this->assertDatabaseHas('newsletter_subscriptions', [
        'email' => 'test@example.com',
        'unsubscribed_at' => null,
    ]);
});
