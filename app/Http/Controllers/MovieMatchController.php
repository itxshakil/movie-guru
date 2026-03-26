<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieMatchSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class MovieMatchController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('MovieMatch', [
            'session' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'picks' => ['required', 'array', 'min:1', 'max:20'],
            'picks.*' => ['required', 'string', 'max:20'],
        ]);

        $session = MovieMatchSession::create([
            'token' => Str::random(32),
            'creator_picks' => $validated['picks'],
            'expires_at' => now()->addHours(24),
        ]);

        return redirect()->route('movie-match.show', $session->token);
    }

    public function show(string $token): Response
    {
        $session = MovieMatchSession::where('token', $token)->firstOrFail();

        if ($session->isExpired()) {
            abort(410, 'This Movie Match session has expired.');
        }

        return Inertia::render('MovieMatch', [
            'session' => [
                'token' => $session->token,
                'creator_picks' => $session->creator_picks,
                'partner_picks' => $session->partner_picks,
                'matched' => $session->matched,
                'expires_at' => $session->expires_at->toISOString(),
            ],
        ]);
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $session = MovieMatchSession::where('token', $token)->firstOrFail();

        if ($session->isExpired()) {
            abort(410, 'This Movie Match session has expired.');
        }

        $validated = $request->validate([
            'picks' => ['required', 'array', 'min:1', 'max:20'],
            'picks.*' => ['required', 'string', 'max:20'],
        ]);

        $partnerPicks = $validated['picks'];
        $matched = array_values(array_intersect($session->creator_picks ?? [], $partnerPicks));

        $session->update([
            'partner_picks' => $partnerPicks,
            'matched' => $matched,
        ]);

        return redirect()->route('movie-match.show', $token);
    }
}
