<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * One title search: the cleaned query, the results page and the optional type and year filters.
 */
final readonly class SearchCriteria
{
    public function __construct(
        public string $search,
        public int $page,
        public ?string $movieType,
        public ?int $year,
    ) {}

    /**
     * The "searcgh" typo is kept on purpose: renaming it would empty every cached search at once.
     */
    public function cacheKey(): string
    {
        return 'searcgh-' . $this->search . '-' . $this->page . '-' . $this->movieType . '-' . $this->year;
    }
}
