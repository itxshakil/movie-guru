<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\WatchModeSourceType;

final readonly class WatchModeSourceMeta
{
    public function __construct(
        public int   $id,
        public string $name,
        public WatchModeSourceType $type,
        public string $logo100px,
        public ?string $iosAppstoreUrl = null,
        public ?string $androidPlaystoreUrl = null,
        /** @var string[] */
        public array $regions = [],
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            name: (string)$data['name'],
            type: WatchModeSourceType::from((string)$data['type']),
            logo100px: (string)$data['logo_100px'],
            iosAppstoreUrl: isset($data['ios_appstore_url']) ? (string)$data['ios_appstore_url'] : null,
            androidPlaystoreUrl: isset($data['android_playstore_url']) ? (string)$data['android_playstore_url'] : null,
            regions: array_map(
                static fn($r): string => (string)$r,
                is_array($data['regions'] ?? null) ? $data['regions'] : [],
            ),
        );
    }
}
