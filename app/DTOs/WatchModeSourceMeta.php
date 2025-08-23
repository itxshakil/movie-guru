<?php
declare(strict_types=1);

namespace App\DTOs;

use App\Enums\WatchModeSourceType;

class WatchModeSourceMeta
{
    public function __construct(
        public readonly int                 $id,
        public readonly string              $name,
        public readonly WatchModeSourceType $type,
        public readonly string              $logo100px,
        public readonly ?string             $iosAppstoreUrl = null,
        public readonly ?string             $androidPlaystoreUrl = null,
        /** @var string[] */
        public readonly array               $regions = []
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            type: WatchModeSourceType::from($data['type']),
            logo100px: $data['logo_100px'],
            iosAppstoreUrl: $data['ios_appstore_url'] ?? null,
            androidPlaystoreUrl: $data['android_playstore_url'] ?? null,
            regions: $data['regions'] ?? []
        );
    }
}
