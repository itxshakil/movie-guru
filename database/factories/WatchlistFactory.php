<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Watchlist>
 */
final class WatchlistFactory extends Factory
{
    protected $model = Watchlist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'imdb_id' => 'tt' . $this->faker->unique()->numerify('#######'),
            'title' => $this->faker->sentence(3),
            'poster' => $this->faker->imageUrl(),
            'year' => (string) $this->faker->year(),
        ];
    }
}
