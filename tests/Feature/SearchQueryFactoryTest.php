<?php

declare(strict_types=1);

use App\Models\SearchQuery;

test('the search query factory always satisfies the table constraints', function (): void {
    $queries = SearchQuery::factory()->count(25)->create();

    expect($queries)->toHaveCount(25)
        ->and($queries->every(fn (SearchQuery $query): bool => $query->page >= 1))->toBeTrue();
    $this->assertDatabaseCount('search_queries', 25);
});
