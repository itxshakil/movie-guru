<?php
declare(strict_types=1);

namespace App\DTOs;

use App\Enums\WatchModeSourceType;

class WatchModeSource
{
    public function __construct(
        public readonly int                 $sourceId,
        public readonly string              $name,
        public readonly WatchModeSourceType $type,
        public readonly string              $region,
        public readonly ?string             $iosUrl = null,
        public readonly ?string             $androidUrl = null,
        public readonly ?string             $webUrl = null,
        public readonly ?string             $format = null,
        public readonly ?float              $price = null,
        public readonly ?int                $seasons = null,
        public readonly ?int                $episodes = null
    )
    {
    }

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
