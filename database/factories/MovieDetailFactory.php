<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MovieDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovieDetail>
 */
final class MovieDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'imdb_id' => 'tt' . $this->faker->unique()->randomNumber(7),
            'title' => $this->faker->sentence(3),
            'year' => (string)$this->faker->year(),
            'release_date' => $this->faker->date(),
            'poster' => $this->faker->imageUrl(),
            'type' => 'movie',
            'imdb_rating' => (string)$this->faker->randomFloat(1, 1, 9),
            'imdb_votes' => (string)$this->faker->numberBetween(1000, 1000000),
            'genre' => 'Action, Drama',
            'director' => $this->faker->name(),
            'writer' => $this->faker->name(),
            'actors' => $this->faker->name() . ', ' . $this->faker->name(),
            'details' => ['Plot' => $this->faker->paragraph()],
            'sources' => [],
            'source_last_fetched_at' => now(),
            'views' => $this->faker->numberBetween(0, 5000),
        ];
    }
}
