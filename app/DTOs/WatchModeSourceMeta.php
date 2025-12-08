<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\WatchModeSourceType;

final readonly class WatchModeSourceMeta
{
    public function __construct(
        public int     $id,
        public string  $name,
        public WatchModeSourceType $type,
        public string  $logo100px,
        public ?string $iosAppstoreUrl = null,
        public ?string $androidPlaystoreUrl = null,
        /** @var string[] */
        public array   $regions = [],
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            type: WatchModeSourceType::from($data['type']),
            logo100px: $data['logo_100px'],
            iosAppstoreUrl: $data['ios_appstore_url'] ?? null,
            androidPlaystoreUrl: $data['android_playstore_url'] ?? null,
            regions: $data['regions'] ?? [],
        );
    }
}
