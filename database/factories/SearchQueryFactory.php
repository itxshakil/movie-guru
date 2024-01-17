<?php

namespace Database\Factories;

use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SearchQueryFactory extends Factory
{
    protected $model = SearchQuery::class;

    public function definition(): array
    {
        return [
            'query' => $this->faker->word(),
            'page' => fake()->optional()->numberBetween(1, 255),
            'type' => fake()->optional()->word(),
            'year' => fake()->optional()->year(),
            'ip_address' => fake()->optional()->ipv4(),
            'user_agent' => fake()->optional()->userAgent(),
            'response_at' => fake()->optional()->dateTimeBetween('+1 seconds', '+2 minutes'),
            'response' => fake()->optional()->boolean(),
        ];
    }
}
