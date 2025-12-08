<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\WatchModeSourceType;

final readonly class WatchModeSource
{
    public function __construct(
        public int     $sourceId,
        public string  $name,
        public WatchModeSourceType $type,
        public string  $region,
        public ?string $iosUrl = null,
        public ?string $androidUrl = null,
        public ?string $webUrl = null,
        public ?string $format = null,
        public ?float  $price = null,
        public ?int    $seasons = null,
        public ?int    $episodes = null,
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sourceId: $data['source_id'],
            name: $data['name'],
            type: WatchModeSourceType::from($data['type']),
            region: $data['region'],
            iosUrl: $data['ios_url'] ?? null,
            androidUrl: $data['android_url'] ?? null,
            webUrl: $data['web_url'] ?? null,
            format: $data['format'] ?? null,
            price: isset($data['price']) ? (float)$data['price'] : null,
            seasons: $data['seasons'] ?? null,
            episodes: $data['episodes'] ?? null,
        );
    }
}
