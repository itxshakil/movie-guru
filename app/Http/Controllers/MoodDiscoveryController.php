<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class MoodDiscoveryController extends Controller
{
    /** @var array<string, array<string, mixed>> */
    private const MOODS = [
        'cozy' => [
            'label' => 'Cozy',
            'emoji' => '🛋️',
            'description' => 'Warm, feel-good films to curl up with.',
            'genres' => ['Comedy', 'Romance', 'Family', 'Animation'],
        ],
        'heartbroken' => [
            'label' => 'Heartbroken',
            'emoji' => '💔',
            'description' => 'Emotional dramas that let you feel it all.',
            'genres' => ['Drama', 'Romance'],
        ],
        'hyped' => [
            'label' => 'Hyped',
            'emoji' => '⚡',
            'description' => 'High-energy action and thrillers.',
            'genres' => ['Action', 'Thriller', 'Adventure'],
        ],
        'bored' => [
            'label' => 'Bored',
            'emoji' => '😴',
            'description' => 'Something surprising to shake things up.',
            'genres' => ['Mystery', 'Sci-Fi', 'Fantasy', 'Horror'],
        ],
        'adventurous' => [
            'label' => 'Adventurous',
            'emoji' => '🌍',
            'description' => 'Epic journeys and world-spanning stories.',
            'genres' => ['Adventure', 'Action', 'Fantasy'],
        ],
    ];

    public function index(): Response
    {
        return Inertia::render('MoodDiscovery', [
            'moods' => array_map(fn($key, $mood) => ['slug' => $key, ...$mood], array_keys(self::MOODS), self::MOODS),
            'selectedMood' => null,
            'movies' => [],
        ]);
    }

    public function show(Request $request, string $mood): Response
    {
        abort_unless(isset(self::MOODS[$mood]), 404);

        $config = self::MOODS[$mood];

        $movies = MovieDetail::query()
            ->where(function ($q) use ($config): void {
                foreach ($config['genres'] as $genre) {
                    $q->orWhere('genre', 'like', "%{$genre}%");
                }
            })
            ->where('imdb_rating', '>=', 6.0)
            ->orderByDesc('imdb_rating')
            ->limit(24)
            ->get(['imdb_id', 'title', 'year', 'poster', 'imdb_rating', 'genre']);

        return Inertia::render('MoodDiscovery', [
            'moods' => array_map(fn($key, $m) => ['slug' => $key, ...$m], array_keys(self::MOODS), self::MOODS),
            'selectedMood' => ['slug' => $mood, ...$config],
            'movies' => $movies,
        ]);
    }
}
