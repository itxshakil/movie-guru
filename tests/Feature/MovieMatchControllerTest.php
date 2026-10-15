<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use App\Models\MovieMatchSession;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

test('the creator sees the share view for their own match, even after their session ends', function (): void {
    $response = $this->post(route('movie-match.store'), ['picks' => ['tt0111161', 'tt0068646']]);

    $session = MovieMatchSession::query()->sole();
    $response->assertRedirect(route('movie-match.show', $session->token));
    $createdCookie = $response->getCookie('movie_match_created');
    expect($createdCookie->getExpiresTime())->toBeGreaterThan(now()->addHours(23)->getTimestamp());

    $this->flushSession();

    $this->withCookie('movie_match_created', $createdCookie->getValue())
        ->get(route('movie-match.show', $session->token))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('MovieMatch')
            ->where('session.is_creator', expected: true));
});

test('a friend opening the shared link sees the pick form, not the creator view', function (): void {
    $session = MovieMatchSession::create([
        'token' => Str::random(32),
        'creator_picks' => ['tt0111161'],
        'expires_at' => now()->addDay(),
    ]);

    $this->get(route('movie-match.show', $session->token))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('session.is_creator', expected: false)
            ->where('session.partner_picks', expected: null));
});

test('submitting picks shows the matched titles with their details', function (): void {
    MovieDetail::factory()->create(['imdb_id' => 'tt0111161', 'title' => 'The Shawshank Redemption', 'year' => '1994']);
    $session = MovieMatchSession::create([
        'token' => Str::random(32),
        'creator_picks' => ['tt0111161', 'tt0000001'],
        'expires_at' => now()->addDay(),
    ]);

    $this->post(route('movie-match.submit', $session->token), ['picks' => ['tt0111161', 'tt0000001', 'tt0068646']])
        ->assertRedirect(route('movie-match.show', $session->token));

    $this->get(route('movie-match.show', $session->token))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('session.matched', ['tt0111161', 'tt0000001'])
            ->where('session.matched_movies.0.title', 'The Shawshank Redemption')
            ->where('session.matched_movies.1.imdb_id', 'tt0000001')
            ->where('session.matched_movies.1.title', expected: null));
});

test('picks are validated', function (): void {
    $this->post(route('movie-match.store'), ['picks' => []])
        ->assertSessionHasErrors('picks');
});

test('expired matches are gone', function (): void {
    $session = MovieMatchSession::create([
        'token' => Str::random(32),
        'creator_picks' => ['tt0111161'],
        'expires_at' => now()->subMinute(),
    ]);

    $this->get(route('movie-match.show', $session->token))->assertGone();
});
