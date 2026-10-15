<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryItem>
 */
final class LibraryItemFactory extends Factory
{
    protected $model = LibraryItem::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'imdb_id' => 'tt' . $this->faker->unique()->numerify('#######'),
            'title' => $this->faker->sentence(3),
            'poster' => $this->faker->imageUrl(),
            'year' => (string) $this->faker->year(),
            'status' => LibraryStatus::ToWatch,
            'reaction' => null,
            'watched_at' => null,
        ];
    }

    public function watched(): self
    {
        return $this->state(fn (): array => [
            'status' => LibraryStatus::Watched,
            'watched_at' => now()->subDay(),
        ]);
    }

    public function liked(): self
    {
        return $this->watched()->state(fn (): array => ['reaction' => LibraryReaction::Liked]);
    }

    public function disliked(): self
    {
        return $this->watched()->state(fn (): array => ['reaction' => LibraryReaction::Disliked]);
    }
}
