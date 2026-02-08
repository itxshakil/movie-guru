<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\WatchModeSourceType;

final readonly class WatchModeSource
{
    public function __construct(
        public int  $sourceId,
        public string $name,
        public WatchModeSourceType $type,
        public string $region,
        public ?string $iosUrl = null,
        public ?string $androidUrl = null,
        public ?string $webUrl = null,
        public ?string $format = null,
        public ?float $price = null,
        public ?int $seasons = null,
        public ?int $episodes = null,
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sourceId: (int)$data['source_id'],
            name: (string)$data['name'],
            type: WatchModeSourceType::from((string)$data['type']),
            region: (string)$data['region'],
            iosUrl: isset($data['ios_url']) ? (string)$data['ios_url'] : null,
            androidUrl: isset($data['android_url']) ? (string)$data['android_url'] : null,
            webUrl: isset($data['web_url']) ? (string)$data['web_url'] : null,
            format: isset($data['format']) ? (string)$data['format'] : null,
            price: isset($data['price']) ? (float)$data['price'] : null,
            seasons: isset($data['seasons']) ? (int)$data['seasons'] : null,
            episodes: isset($data['episodes']) ? (int)$data['episodes'] : null,
        );
    }
}
