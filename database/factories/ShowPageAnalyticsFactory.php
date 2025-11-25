<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShowPageAnalytics;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ShowPageAnalyticsFactory extends Factory
{
    protected $model = ShowPageAnalytics::class;

    public function definition(): array
    {
        return [
            'imdb_id' => fake()->word(),
            'ip_address' => fake()->optional()->ipv4(),
            'user_agent' => fake()->optional()->userAgent(),
        ];
    }
}
