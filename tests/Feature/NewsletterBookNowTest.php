<?php

declare(strict_types=1);

use App\Mail\NewsletterMail;
use App\Mail\WelcomeSubscriberMail;
use App\Models\MovieDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders book now button when affiliate link is present', function (): void {
    $affiliateLink = ['link' => 'https://pvr.com', 'title' => 'PVR'];
    $movie = MovieDetail::factory()->create([
        'affiliate_link' => $affiliateLink,
    ]);
    $movie->refresh();

    $mail = new NewsletterMail(
        type: 'weekly',
        movies: collect([$movie]),
        email: 'test@example.com',
        unsubscribeUrl: 'https://example.com/unsubscribe',
    );

    $html = $mail->render();
    expect($html)->toContain('Book');
    expect($html)->toContain('Now');
    expect($html)->toContain('https://pvr.com');
    expect($html)->toContain('utm_source=newsletter');
});

it('does not render book now button when affiliate link is missing', function (): void {
    $movie = MovieDetail::factory()->create([
        'affiliate_link' => null,
    ]);

    $mail = new NewsletterMail(
        type: 'weekly',
        movies: collect([$movie]),
        email: 'test@example.com',
        unsubscribeUrl: 'https://example.com/unsubscribe',
    );

    $html = $mail->render();

    expect($html)->not->toContain('Book Now');
});

it('renders book now button for special selections when affiliate link is present', function (): void {
    $affiliateLink = ['link' => 'https://pvr.com', 'title' => 'PVR'];
    $trendingMovie = MovieDetail::factory()->create(['affiliate_link' => $affiliateLink]);
    $recommendedMovie = MovieDetail::factory()->create(['affiliate_link' => $affiliateLink]);
    $hiddenGem = MovieDetail::factory()->create(['affiliate_link' => $affiliateLink]);

    $trendingMovie->refresh();
    $recommendedMovie->refresh();
    $hiddenGem->refresh();

    $mail = new NewsletterMail(
        type: 'weekly',
        movies: collect(),
        email: 'test@example.com',
        recommendedMovie: $recommendedMovie,
        hiddenGem: $hiddenGem,
        trendingMovie: $trendingMovie,
        unsubscribeUrl: 'https://example.com/unsubscribe',
    );

    $html = $mail->render();

    // Check if "Book Now" appears multiple times (one for each special selection)
    $count = mb_substr_count($html, 'Book');
    expect($count)->toBe(3);
    expect($html)->toContain('utm_source=newsletter');
});

it('keeps a gap between the view details and book now buttons when they wrap or stack on mobile', function (): void {
    $movie = MovieDetail::factory()->create(['affiliate_link' => ['link' => 'https://pvr.com', 'title' => 'PVR']]);
    $movie->refresh();

    $mail = new NewsletterMail(
        type: 'weekly',
        movies: collect([$movie]),
        email: 'test@example.com',
        unsubscribeUrl: 'https://example.com/unsubscribe',
    );

    $html = $mail->render();

    expect($html)
        ->toContain('@media only screen and (max-width: 480px)')
        ->toContain('.mg-btn')
        ->not->toContain('margin-left:6px')
        ->and(mb_substr_count($html, 'class="mg-btn"'))->toBe(2)
        ->and(mb_substr_count($html, 'margin:0 8px 8px 0'))->toBe(2);
});

it('renders the welcome mail with mobile styles and a signed unsubscribe link', function (): void {
    $html = new WelcomeSubscriberMail('Ada', 'ada@example.com')->render();

    expect($html)
        ->toContain('Hi Ada, great to have you.')
        ->toContain('@media only screen and (max-width: 480px)')
        ->toContain('unsubscribe')
        ->toContain('signature=');
});
