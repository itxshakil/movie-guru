<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MovieMatchPicksRequest;
use App\Models\MovieDetail;
use App\Models\MovieMatchSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use SensitiveParameter;

final class MovieMatchController extends Controller
{
    /**
     * Cookie holding the tokens of matches created in this browser, so the creator sees the
     * "share this link" view while the friend opening the same link sees the pick form. A cookie
     * (encrypted by Laravel) rather than the session, because matches outlive the session.
     */
    private const string CREATED_TOKENS_COOKIE = 'movie_match_created';

    private const int MATCH_LIFETIME_HOURS = 24;

    private const int MAX_REMEMBERED_MATCHES = 20;

    public function create(): Response
    {
        return Inertia::render('MovieMatch', [
            'session' => null,
        ]);
    }

    public function store(MovieMatchPicksRequest $request): RedirectResponse
    {
        /** @var array{picks: array<int, string>} $validated */
        $validated = $request->validated();

        $session = MovieMatchSession::create([
            'token' => Str::random(32),
            'creator_picks' => $validated['picks'],
            'expires_at' => now()->addHours(self::MATCH_LIFETIME_HOURS),
        ]);

        $createdTokens = array_slice(
            [...$this->createdTokens($request), $session->token],
            -self::MAX_REMEMBERED_MATCHES,
        );

        return redirect()
            ->route('movie-match.show', $session->token)
            ->withCookie(cookie(
                self::CREATED_TOKENS_COOKIE,
                (string) json_encode($createdTokens),
                self::MATCH_LIFETIME_HOURS * 60,
            ));
    }

    public function show(Request $request, #[SensitiveParameter] string $token): Response
    {
        $session = MovieMatchSession::query()->where('token', $token)->firstOrFail();

        if ($session->isExpired()) {
            abort(410, 'This Movie Match session has expired.');
        }

        return Inertia::render('MovieMatch', [
            'session' => [
                'token' => $session->token,
                'is_creator' => in_array($session->token, $this->createdTokens($request), true),
                'creator_picks' => $session->creator_picks,
                'partner_picks' => $session->partner_picks,
                'matched' => $session->matched,
                'matched_movies' => $this->matchedMovies($session->matched ?? []),
                'expires_at' => $session->expires_at->toISOString(),
            ],
        ]);
    }

    public function submit(MovieMatchPicksRequest $request, #[SensitiveParameter] string $token): RedirectResponse
    {
        $session = MovieMatchSession::query()->where('token', $token)->firstOrFail();

        if ($session->isExpired()) {
            abort(410, 'This Movie Match session has expired.');
        }

        /** @var array{picks: array<int, string>} $validated */
        $validated = $request->validated();

        $partnerPicks = $validated['picks'];
        $matched = array_values(array_intersect($session->creator_picks ?? [], $partnerPicks));

        $session->update([
            'partner_picks' => $partnerPicks,
            'matched' => $matched,
        ]);

        return redirect()->route('movie-match.show', $token);
    }

    /**
     * @return array<int, string>
     */
    private function createdTokens(Request $request): array
    {
        $decoded = json_decode((string) $request->cookie(self::CREATED_TOKENS_COOKIE, '[]'), true);

        return is_array($decoded) ? array_values(array_filter($decoded, is_string(...))) : [];
    }

    /**
     * Titles and posters for the matched ids, in match order; ids we have no details for keep just the id.
     *
     * @param  array<int, string>                                                                             $matchedIds
     * @return array<int, array{imdb_id: string, title: string|null, year: string|null, poster: string|null}>
     */
    private function matchedMovies(array $matchedIds): array
    {
        if ($matchedIds === []) {
            return [];
        }

        $movies = MovieDetail::query()
            ->whereIn('imdb_id', $matchedIds)
            ->get(['imdb_id', 'title', 'year', 'poster'])
            ->keyBy('imdb_id');

        return array_map(static function (string $imdbId) use ($movies): array {
            $movie = $movies->get($imdbId);

            return [
                'imdb_id' => $imdbId,
                'title' => $movie?->title,
                'year' => $movie?->year,
                'poster' => $movie?->poster,
            ];
        }, $matchedIds);
    }
}
