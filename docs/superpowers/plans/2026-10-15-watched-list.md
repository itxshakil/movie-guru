# Watched List Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let guests and members mark titles as watched (with an optional like or dislike), shown as a "Watched" tab next to "To watch", by turning the watchlist into one library with a status.

**Architecture:** The `watchlists` table becomes `library_items` with `status`, `reaction` and `watched_at`. A `/library` JSON API (index, upsert, delete, import) replaces the `/my-watchlist` JSON routes, with the state rules in one Action. On the client, a pure `libraryState.js` module holds the state rules and is tested with Node's built-in test runner. `useLibrary.js` owns IndexedDB (guests), the API (members), the sign-in merge and offline caching, and `useWatchlist.js` becomes a thin wrapper so existing callers keep working.

**Tech Stack:** Laravel 13, PHP 8.4, Pest 4, Inertia 2 with Vue 3 (`<script setup>`), Tailwind 4, IndexedDB, a service worker at `public/sw.js`, and Node's built-in `node:test` for pure JS.

**Spec:** `docs/superpowers/specs/2026-10-15-watched-list-design.md`

## Global Constraints

- Every PHP file starts with `declare(strict_types=1);`, and classes are `final` with typed properties and return types.
- Validation goes in Form Requests and responses in API Resources; controllers stay thin, and business rules live in `app/Actions`.
- Use `Model::query()->...`, never static query calls on models (mago `no-implicit-model-query`).
- Never use the em dash character "—" in code, comments, copy or commit messages; use "-".
- Do not add composer or npm dependencies. The JS tests use Node's built-in `node:test` and `node:assert/strict`.
- No `console.log` in committed code; `console.error` for real failures is fine.
- Every commit must pass `composer lint`, `composer test` and `npm run build`. Run `vendor/bin/pint --dirty --format agent` after PHP edits.
- Commit messages are conventional (`feat:`, `fix:`, `refactor:`, `test:`), end with `Refs #22`, and must not add a co-author line.
- Timestamps on the client (`saved_at`, `watched_at`) are ISO 8601 strings everywhere: in IndexedDB, in memory and over the API.
- Status values are exactly `to_watch` and `watched`; reaction values are exactly `liked` and `disliked`, or `null`.

## Review Focus

- A guest whose IndexedDB still holds the version 1 `watchlist` store with numeric `saved_at` values opens the new version: every title must appear under "To watch" with nothing lost (Task 4 tests `upgradeLegacyItem`, Task 5 performs the upgrade).
- A member double-taps "Watched" or taps it while the previous request is in flight: the final state must be watched, with no duplicate row and no stale revert (Task 2 makes `PUT` an idempotent upsert; Task 5 reverts only to the snapshot taken before that action).
- A guest liked a title on the device and also has it as "to watch" in the account, then signs in: the account must end up watched and liked (Task 3 merge test).
- Someone sends a reaction for a title that is not watched, through the API or a stale tab: it must be rejected with 422 on the server and refused by `applyLibraryChange` on the client (Tasks 2 and 4).
- `/watchlist?tab=watched` is opened directly, offline, or with an unknown `tab` value: it must render the Watched tab, or fall back to "To watch" for unknown values (Task 8 `activeTab` fallback, Task 6 offline cache of `/watchlist`).

---

## File Structure

Server:
- Create `app/Enums/LibraryStatus.php` and `app/Enums/LibraryReaction.php` (backed enums).
- Create the migration `database/migrations/2026_10_15_000000_convert_watchlists_to_library_items.php` (rename and add columns).
- Rename `app/Models/Watchlist.php` to `app/Models/LibraryItem.php`, `app/Policies/WatchlistPolicy.php` to `app/Policies/LibraryItemPolicy.php`, and `database/factories/WatchlistFactory.php` to `database/factories/LibraryItemFactory.php`.
- Create `app/Actions/UpdateLibraryItem.php` (the single home of the state rules).
- Create `app/Actions/MergeGuestLibrary.php`, replacing `app/Actions/MergeGuestWatchlist.php`.
- Create `app/Http/Requests/LibraryItemRequest.php` and `app/Http/Requests/LibraryImportRequest.php`, replacing `WatchlistStoreRequest.php` and `WatchlistImportRequest.php`.
- Create `app/Http/Resources/LibraryItemResource.php`, replacing `WatchlistItemResource.php`.
- Create `app/Http/Controllers/LibraryController.php` (index, update, destroy) and `app/Http/Controllers/LibraryImportController.php` (store), replacing `WatchlistController.php` and `WatchlistImportController.php`.
- Modify `routes/web.php`, `app/Services/AdminDigestService.php` and `tests/Feature/Console/Commands/SendAdminDigestCommandTest.php`.
- Tests: create `tests/Feature/LibraryMigrationTest.php`, `tests/Feature/LibraryControllerTest.php` and `tests/Feature/LibraryImportControllerTest.php`; delete `tests/Feature/WatchlistControllerTest.php`.

Client:
- Create `resources/js/Composables/libraryState.js` (pure state rules, no Vue or browser APIs) and `tests/js/libraryState.test.mjs` (Node tests).
- Create `resources/js/Composables/useLibrary.js`.
- Modify `resources/js/Composables/useWatchlist.js` into a thin wrapper, and update `resources/js/app.js` imports.
- Modify `public/sw.js`.
- Create `resources/js/Components/WatchedButton.vue`.
- Modify `resources/js/Components/SearchCard.vue`, `resources/js/Components/DetailCard.vue` and `resources/js/Pages/Watchlist.vue`.
- Modify `package.json` (add a `test:js` script).

---

### Task 1: Library data model (enums, migration, model, factory, policy)

**Files:**
- Create: `app/Enums/LibraryStatus.php`
- Create: `app/Enums/LibraryReaction.php`
- Create: `database/migrations/2026_10_15_000000_convert_watchlists_to_library_items.php`
- Create (via `git mv`): `app/Models/LibraryItem.php`, `app/Policies/LibraryItemPolicy.php`, `database/factories/LibraryItemFactory.php`
- Modify: `app/Services/AdminDigestService.php:13,45`
- Modify: `tests/Feature/Console/Commands/SendAdminDigestCommandTest.php:11,45`
- Modify: `routes/web.php` (remove the four `/my-watchlist` routes, add a permanent redirect)
- Delete: `app/Http/Controllers/WatchlistController.php`, `app/Http/Controllers/WatchlistImportController.php`, `app/Http/Requests/WatchlistStoreRequest.php`, `app/Http/Requests/WatchlistImportRequest.php`, `app/Http/Resources/WatchlistItemResource.php`, `app/Actions/MergeGuestWatchlist.php`, `tests/Feature/WatchlistControllerTest.php`
- Test: `tests/Feature/LibraryMigrationTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `App\Enums\LibraryStatus` with cases `ToWatch = 'to_watch'` and `Watched = 'watched'`.
  - `App\Enums\LibraryReaction` with cases `Liked = 'liked'` and `Disliked = 'disliked'`.
  - `App\Models\LibraryItem`, with fillable `user_id, imdb_id, title, poster, year, status, reaction, watched_at`, casts `status => LibraryStatus`, `reaction => LibraryReaction|null`, `watched_at => datetime`, and the scope `forUser(User $user)`.
  - `LibraryItem::factory()` with the states `watched()`, `liked()` and `disliked()`.
  - The `library_items` table.

- [ ] **Step 1: Write the failing migration test**

Create `tests/Feature/LibraryMigrationTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function convertWatchlistsMigration(): object
{
    return require database_path('migrations/2026_10_15_000000_convert_watchlists_to_library_items.php');
}

test('existing watchlist rows become to watch library items', function (): void {
    $user = User::factory()->create();
    $migration = convertWatchlistsMigration();
    $migration->down();

    DB::table('watchlists')->insert([
        'user_id' => $user->id,
        'imdb_id' => 'tt0111161',
        'title' => 'The Shawshank Redemption',
        'poster' => null,
        'year' => '1994',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration->up();

    expect(Schema::hasTable('watchlists'))->toBeFalse();
    $item = LibraryItem::query()->sole();
    expect($item->imdb_id)->toBe('tt0111161')
        ->and($item->status)->toBe(LibraryStatus::ToWatch)
        ->and($item->reaction)->toBeNull()
        ->and($item->watched_at)->toBeNull();
});

test('a title can only be in a member library once', function (): void {
    $item = LibraryItem::factory()->create();

    expect(fn () => LibraryItem::factory()->create(['user_id' => $item->user_id, 'imdb_id' => $item->imdb_id]))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

test('library items scope to their member', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create();
    LibraryItem::factory()->create();

    expect(LibraryItem::query()->forUser($user)->count())->toBe(1);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=LibraryMigrationTest`
Expected: FAIL, because the migration file and `App\Models\LibraryItem` do not exist.

- [ ] **Step 3: Create the enums**

`app/Enums/LibraryStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum LibraryStatus: string
{
    case ToWatch = 'to_watch';
    case Watched = 'watched';
}
```

`app/Enums/LibraryReaction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum LibraryReaction: string
{
    case Liked = 'liked';
    case Disliked = 'disliked';
}
```

- [ ] **Step 4: Create the migration**

`database/migrations/2026_10_15_000000_convert_watchlists_to_library_items.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The watchlist becomes a library: every saved title has a status ("to_watch" or "watched"),
 * and watched titles can carry a like or dislike. Existing rows become "to_watch".
 * Rolling back drops watched state and reactions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('watchlists', 'library_items');

        Schema::table('library_items', function (Blueprint $table): void {
            $table->string('status', 16)->default('to_watch')->after('year');
            $table->string('reaction', 16)->nullable()->after('status');
            $table->timestamp('watched_at')->nullable()->after('reaction');

            $table->index(['user_id', 'status'], 'library_items_user_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('library_items', function (Blueprint $table): void {
            $table->dropIndex('library_items_user_id_status_index');
            $table->dropColumn(['status', 'reaction', 'watched_at']);
        });

        Schema::rename('library_items', 'watchlists');
    }
};
```

- [ ] **Step 5: Rename the model, policy and factory**

```bash
git mv app/Models/Watchlist.php app/Models/LibraryItem.php
git mv app/Policies/WatchlistPolicy.php app/Policies/LibraryItemPolicy.php
git mv database/factories/WatchlistFactory.php database/factories/LibraryItemFactory.php
```

Replace `app/Models/LibraryItem.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A title in a member's library: on their watchlist ("to_watch") or already watched.
 *
 * @property LibraryStatus $status
 * @property LibraryReaction|null $reaction
 * @property Carbon|null $watched_at
 */
final class LibraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'imdb_id',
        'title',
        'poster',
        'year',
        'status',
        'reaction',
        'watched_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param Builder<self> $query
     */
    #[Scope]
    protected function forUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    protected function casts(): array
    {
        return [
            'status' => LibraryStatus::class,
            'reaction' => LibraryReaction::class,
            'watched_at' => 'datetime',
        ];
    }
}
```

Replace `app/Policies/LibraryItemPolicy.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LibraryItem;
use App\Models\User;

final class LibraryItemPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, LibraryItem $libraryItem): bool
    {
        return $user->id === $libraryItem->user_id;
    }

    public function delete(User $user, LibraryItem $libraryItem): bool
    {
        return $user->id === $libraryItem->user_id;
    }
}
```

Replace `database/factories/LibraryItemFactory.php` with:

```php
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
```

- [ ] **Step 6: Point the admin digest at the new model**

In `app/Services/AdminDigestService.php`, replace `use App\Models\Watchlist;` with `use App\Models\LibraryItem;`, and replace `Watchlist::query()->whereBetween(...)` on line 45 with `LibraryItem::query()->where('status', LibraryStatus::ToWatch)->whereBetween('created_at', [$start, $end])->count()`, adding `use App\Enums\LibraryStatus;`. The digest's "watchlist adds" keeps counting to-watch saves only.

In `tests/Feature/Console/Commands/SendAdminDigestCommandTest.php`, replace `use App\Models\Watchlist;` with `use App\Models\LibraryItem;` and `Watchlist::factory()` with `LibraryItem::factory()`.

- [ ] **Step 7: Remove the old watchlist API**

The old JSON API is replaced by `/library` in Tasks 2 and 3, and it references the renamed model, so remove it now to keep this commit green. Until Task 5, signed-in members' watchlist saves fail with a toast; guests are unaffected. This is acceptable inside the feature branch.

```bash
git rm app/Http/Controllers/WatchlistController.php app/Http/Controllers/WatchlistImportController.php app/Http/Requests/WatchlistStoreRequest.php app/Http/Requests/WatchlistImportRequest.php app/Http/Resources/WatchlistItemResource.php app/Actions/MergeGuestWatchlist.php tests/Feature/WatchlistControllerTest.php
```

In `routes/web.php`, delete the `use App\Http\Controllers\WatchlistController;` and `use App\Http\Controllers\WatchlistImportController;` imports and these four routes from the `auth` group:

```php
    Route::get('/my-watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/my-watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/my-watchlist/{imdbId}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
    Route::post('/my-watchlist/import', [WatchlistImportController::class, 'store'])->name('watchlist.import.store');
```

Add next to the `Route::inertia('/watchlist', ...)` line, outside the `auth` group:

```php
Route::permanentRedirect('/my-watchlist', '/watchlist');
```

Add to `tests/Feature/LibraryMigrationTest.php`:

```php
test('the old member watchlist url opens the watchlist page', function (): void {
    $this->get('/my-watchlist')->assertRedirect('/watchlist');
});
```

- [ ] **Step 8: Run the tests to verify they pass**

Run: `php artisan test --compact`
Expected: PASS (the whole suite).

- [ ] **Step 9: Lint and commit**

```bash
vendor/bin/pint --dirty --format agent
composer lint
git add -A app database routes tests
git commit -m "feat: turn the watchlist table into a library with watched status

Refs #22"
```

---

### Task 2: Library API (index, upsert, delete) and the state rules

**Files:**
- Create: `app/Actions/UpdateLibraryItem.php`
- Create: `app/Http/Requests/LibraryItemRequest.php`
- Create: `app/Http/Resources/LibraryItemResource.php`
- Create: `app/Http/Controllers/LibraryController.php`
- Modify: `routes/web.php` (the `auth` group)
- Test: `tests/Feature/LibraryControllerTest.php`

**Interfaces:**
- Consumes: `LibraryItem`, `LibraryStatus` and `LibraryReaction` from Task 1.
- Produces:
  - `App\Actions\UpdateLibraryItem::handle(User $user, string $imdbId, array{title: string, poster?: ?string, year?: ?string, status: string, reaction?: ?string} $attributes): LibraryItem`
  - `App\Http\Resources\LibraryItemResource`, which serializes to `{imdb_id, title, poster, year, status, reaction, watched_at, saved_at}`. `status` and `reaction` are enum string values; `watched_at` and `saved_at` are ISO 8601 strings, with `watched_at` null when not watched.
  - Routes:
    - `library.index`: `GET /library` (JSON; non-JSON visits redirect to `route('watchlist')`).
    - `library.update`: `PUT /library/{imdbId}`.
    - `library.destroy`: `DELETE /library/{imdbId}`.

- [ ] **Step 1: Write the failing API tests**

Create `tests/Feature/LibraryControllerTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;

function libraryPayload(array $overrides = []): array
{
    return [
        'title' => 'Inception',
        'poster' => 'https://example.com/inception.jpg',
        'year' => '2010',
        'status' => 'to_watch',
        'reaction' => null,
        ...$overrides,
    ];
}

test('members get their whole library as json', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create(['imdb_id' => 'tt1375666']);
    LibraryItem::factory()->for($user)->liked()->create(['imdb_id' => 'tt0816692']);
    LibraryItem::factory()->create(['imdb_id' => 'tt0068646']);

    $this->actingAs($user)
        ->getJson(route('library.index'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['imdb_id', 'title', 'poster', 'year', 'status', 'reaction', 'watched_at', 'saved_at']]])
        ->assertJsonFragment(['imdb_id' => 'tt0816692', 'status' => 'watched', 'reaction' => 'liked']);
});

test('plain visits to the library open the watchlist page', function (): void {
    $this->actingAs(User::factory()->create())->get(route('library.index'))->assertRedirect(route('watchlist'));
});

test('guests cannot use the library api', function (): void {
    $this->getJson(route('library.index'))->assertUnauthorized();
    $this->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload())->assertUnauthorized();
    $this->deleteJson(route('library.destroy', ['imdbId' => 'tt1375666']))->assertUnauthorized();
});

test('saving a title adds it to the watchlist', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload())
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'to_watch')
        ->assertJsonPath('data.watched_at', null);

    $this->assertDatabaseHas('library_items', ['user_id' => $user->id, 'imdb_id' => 'tt1375666', 'status' => 'to_watch']);
});

test('marking a watchlist title watched moves it in place and stamps watched_at', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched', 'reaction' => 'liked']))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'watched')
        ->assertJsonPath('data.reaction', 'liked');

    $item = LibraryItem::query()->forUser($user)->sole();
    expect($item->status)->toBe(LibraryStatus::Watched)
        ->and($item->reaction)->toBe(LibraryReaction::Liked)
        ->and($item->watched_at)->not->toBeNull();
});

test('changing the reaction keeps the original watched date', function (): void {
    $user = User::factory()->create();
    $item = LibraryItem::factory()->for($user)->watched()->create(['imdb_id' => 'tt1375666', 'watched_at' => '2026-01-02 03:04:05']);

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched', 'reaction' => 'disliked']))
        ->assertSuccessful();

    expect($item->refresh()->watched_at->toDateTimeString())->toBe('2026-01-02 03:04:05')
        ->and($item->reaction)->toBe(LibraryReaction::Disliked);
});

test('moving a watched title back to the watchlist clears its reaction and watched date', function (): void {
    $user = User::factory()->create();
    $item = LibraryItem::factory()->for($user)->liked()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'to_watch']))
        ->assertSuccessful();

    $item->refresh();
    expect($item->status)->toBe(LibraryStatus::ToWatch)
        ->and($item->reaction)->toBeNull()
        ->and($item->watched_at)->toBeNull();
});

test('saving the same title twice keeps one row', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched']));
    $this->actingAs($user)->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched']));

    expect(LibraryItem::query()->forUser($user)->count())->toBe(1);
});

test('a reaction needs the title to be watched', function (): void {
    $this->actingAs(User::factory()->create())
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'to_watch', 'reaction' => 'liked']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('reaction');
});

test('library updates are validated', function (array $payload, string $invalidField): void {
    $this->actingAs(User::factory()->create())
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($invalidField);
})->with([
    'missing title' => [['status' => 'to_watch'], 'title'],
    'unknown status' => [['title' => 'Inception', 'status' => 'abandoned'], 'status'],
    'unknown reaction' => [['title' => 'Inception', 'status' => 'watched', 'reaction' => 'meh'], 'reaction'],
]);

test('only imdb ids are accepted in library urls', function (): void {
    $this->actingAs(User::factory()->create())
        ->putJson('/library/not-an-id', libraryPayload())
        ->assertNotFound();
});

test('members can remove only their own titles', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    LibraryItem::factory()->for($user)->watched()->create(['imdb_id' => 'tt1375666']);
    LibraryItem::factory()->for($otherUser)->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)->deleteJson(route('library.destroy', ['imdbId' => 'tt1375666']))->assertNoContent();

    $this->assertDatabaseMissing('library_items', ['user_id' => $user->id, 'imdb_id' => 'tt1375666']);
    $this->assertDatabaseHas('library_items', ['user_id' => $otherUser->id, 'imdb_id' => 'tt1375666']);
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact --filter=LibraryControllerTest`
Expected: FAIL, with route `library.index` not defined.

- [ ] **Step 3: Write the state rules Action**

`app/Actions/UpdateLibraryItem.php`:

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;

/**
 * Saves a title in a member's library and applies the library state rules:
 * becoming watched stamps watched_at (kept on later edits), a reaction only exists on
 * watched titles, and going back to "to_watch" clears both.
 */
final class UpdateLibraryItem
{
    /**
     * @param array{title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null} $attributes
     */
    public function handle(User $user, string $imdbId, array $attributes): LibraryItem
    {
        $item = LibraryItem::query()->firstOrNew(['user_id' => $user->id, 'imdb_id' => $imdbId]);
        $status = LibraryStatus::from($attributes['status']);
        $wasWatched = $item->exists && $item->status === LibraryStatus::Watched;

        $item->fill([
            'title' => $attributes['title'],
            'poster' => $attributes['poster'] ?? null,
            'year' => $attributes['year'] ?? null,
            'status' => $status,
        ]);

        if ($status === LibraryStatus::Watched) {
            $item->watched_at = $wasWatched ? $item->watched_at : now();
            $item->reaction = isset($attributes['reaction']) ? LibraryReaction::from($attributes['reaction']) : null;
        } else {
            $item->watched_at = null;
            $item->reaction = null;
        }

        $item->save();

        return $item;
    }
}
```

- [ ] **Step 4: Write the request and resource**

`app/Http/Requests/LibraryItemRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LibraryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'poster' => ['nullable', 'string', 'max:500'],
            'year' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::enum(LibraryStatus::class)],
            'reaction' => ['nullable', Rule::enum(LibraryReaction::class), 'prohibited_unless:status,watched'],
        ];
    }
}
```

`app/Http/Resources/LibraryItemResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LibraryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LibraryItem
 */
final class LibraryItemResource extends JsonResource
{
    /**
     * @return array{imdb_id: string, title: string, poster: string|null, year: string|null, status: string, reaction: string|null, watched_at: string|null, saved_at: string|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'imdb_id' => $this->imdb_id,
            'title' => $this->title,
            'poster' => $this->poster,
            'year' => $this->year,
            'status' => $this->status->value,
            'reaction' => $this->reaction?->value,
            'watched_at' => $this->watched_at?->toIso8601String(),
            'saved_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 5: Write the controller**

`app/Http/Controllers/LibraryController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateLibraryItem;
use App\Http\Requests\LibraryItemRequest;
use App\Http\Resources\LibraryItemResource;
use App\Models\LibraryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class LibraryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|RedirectResponse
    {
        // The page lives at /watchlist for guests and members alike; this is its data source.
        if (! $request->wantsJson()) {
            return to_route('watchlist');
        }

        return LibraryItemResource::collection(
            LibraryItem::query()->forUser($request->user())->latest()->get(),
        );
    }

    public function update(LibraryItemRequest $request, string $imdbId, UpdateLibraryItem $updateLibraryItem): LibraryItemResource
    {
        /** @var array{title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null} $validated */
        $validated = $request->validated();

        return new LibraryItemResource($updateLibraryItem->handle($request->user(), $imdbId, $validated));
    }

    public function destroy(Request $request, string $imdbId): Response
    {
        LibraryItem::query()->forUser($request->user())->where('imdb_id', $imdbId)->delete();

        return response()->noContent();
    }
}
```

- [ ] **Step 6: Add the routes**

In `routes/web.php`, add `use App\Http\Controllers\LibraryController;` and, inside the `auth` group:

```php
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::put('/library/{imdbId}', [LibraryController::class, 'update'])
        ->where('imdbId', 'tt[0-9]{7,8}')
        ->name('library.update');
    Route::delete('/library/{imdbId}', [LibraryController::class, 'destroy'])
        ->where('imdbId', 'tt[0-9]{7,8}')
        ->name('library.destroy');
```

- [ ] **Step 7: Run the tests to verify they pass**

Run: `php artisan test --compact`
Expected: PASS (the whole suite).

- [ ] **Step 8: Lint and commit**

```bash
vendor/bin/pint --dirty --format agent
composer lint
git add -A app routes tests
git commit -m "feat: add the library api with watched status and reactions

Refs #22"
```

---

### Task 3: Merge a guest library on sign-in

**Files:**
- Create: `app/Actions/MergeGuestLibrary.php`
- Create: `app/Http/Requests/LibraryImportRequest.php`
- Create: `app/Http/Controllers/LibraryImportController.php`
- Modify: `routes/web.php` (the `auth` group)
- Test: `tests/Feature/LibraryImportControllerTest.php`

**Interfaces:**
- Consumes: `LibraryItem`, `LibraryStatus`, `LibraryReaction` and `LibraryItemResource` from Tasks 1 and 2.
- Produces:
  - Route `library.import.store`: `POST /library/import` with body `{items: [{imdb_id, title, poster, year, status, reaction, watched_at}]}`, returning the whole merged library as a `LibraryItemResource` collection.
  - `App\Actions\MergeGuestLibrary::handle(User $user, array $items): void`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/LibraryImportControllerTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;

function guestItem(array $overrides = []): array
{
    return [
        'imdb_id' => 'tt1375666',
        'title' => 'Inception',
        'poster' => null,
        'year' => '2010',
        'status' => 'to_watch',
        'reaction' => null,
        'watched_at' => null,
        ...$overrides,
    ];
}

test('titles new to the account are added as sent', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [
            guestItem(),
            guestItem(['imdb_id' => 'tt0816692', 'title' => 'Interstellar', 'status' => 'watched', 'reaction' => 'liked', 'watched_at' => '2026-10-01T20:00:00+00:00']),
        ]])
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $watched = LibraryItem::query()->forUser($user)->where('imdb_id', 'tt0816692')->sole();
    expect($watched->status)->toBe(LibraryStatus::Watched)
        ->and($watched->reaction)->toBe(LibraryReaction::Liked)
        ->and($watched->watched_at->toIso8601String())->toBe('2026-10-01T20:00:00+00:00');
});

test('watched on the device beats to watch in the account', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched', 'reaction' => 'liked', 'watched_at' => '2026-10-01T20:00:00+00:00'])]])
        ->assertSuccessful();

    $item = LibraryItem::query()->forUser($user)->sole();
    expect($item->status)->toBe(LibraryStatus::Watched)->and($item->reaction)->toBe(LibraryReaction::Liked);
});

test('the account reaction wins over the device reaction', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->disliked()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched', 'reaction' => 'liked'])]])
        ->assertSuccessful();

    expect(LibraryItem::query()->forUser($user)->sole()->reaction)->toBe(LibraryReaction::Disliked);
});

test('a device reaction fills an account title that has none', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->watched()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched', 'reaction' => 'liked'])]])
        ->assertSuccessful();

    expect(LibraryItem::query()->forUser($user)->sole()->reaction)->toBe(LibraryReaction::Liked);
});

test('to watch on the device never undoes watched in the account', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->liked()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem()]])
        ->assertSuccessful();

    $item = LibraryItem::query()->forUser($user)->sole();
    expect($item->status)->toBe(LibraryStatus::Watched)->and($item->reaction)->toBe(LibraryReaction::Liked);
});

test('a watched title without a date gets one', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched'])]])
        ->assertSuccessful();

    expect(LibraryItem::query()->forUser($user)->sole()->watched_at)->not->toBeNull();
});

test('imported items are validated', function (): void {
    $this->actingAs(User::factory()->create())
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'to_watch', 'reaction' => 'liked'])]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.reaction');
});

test('guests cannot import a library', function (): void {
    $this->postJson(route('library.import.store'), ['items' => [guestItem()]])->assertUnauthorized();
});
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact --filter=LibraryImportControllerTest`
Expected: FAIL, with route `library.import.store` not defined.

- [ ] **Step 3: Write the merge Action**

`app/Actions/MergeGuestLibrary.php`:

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Merges a guest's on-device library into their account after they sign in.
 * Watched beats to watch, and an existing account reaction is never overwritten.
 */
final class MergeGuestLibrary
{
    /**
     * @param array<int, array{imdb_id: string, title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null, watched_at?: string|null}> $items
     */
    public function handle(User $user, array $items): void
    {
        DB::transaction(function () use ($user, $items): void {
            foreach ($items as $item) {
                $this->mergeItem($user, $item);
            }
        });
    }

    /**
     * @param array{imdb_id: string, title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null, watched_at?: string|null} $item
     */
    private function mergeItem(User $user, array $item): void
    {
        $isWatched = LibraryStatus::from($item['status']) === LibraryStatus::Watched;
        $reaction = $isWatched && isset($item['reaction']) ? LibraryReaction::from($item['reaction']) : null;
        $watchedAt = $isWatched ? (isset($item['watched_at']) ? Carbon::parse($item['watched_at']) : now()) : null;

        $existing = LibraryItem::query()->forUser($user)->where('imdb_id', $item['imdb_id'])->first();

        if ($existing === null) {
            LibraryItem::query()->create([
                'user_id' => $user->id,
                'imdb_id' => $item['imdb_id'],
                'title' => $item['title'],
                'poster' => $item['poster'] ?? null,
                'year' => $item['year'] ?? null,
                'status' => $isWatched ? LibraryStatus::Watched : LibraryStatus::ToWatch,
                'reaction' => $reaction,
                'watched_at' => $watchedAt,
            ]);

            return;
        }

        if (! $isWatched) {
            return;
        }

        if ($existing->status === LibraryStatus::ToWatch) {
            $existing->update(['status' => LibraryStatus::Watched, 'reaction' => $reaction, 'watched_at' => $watchedAt]);

            return;
        }

        if ($existing->reaction === null && $reaction !== null) {
            $existing->update(['reaction' => $reaction]);
        }
    }
}
```

- [ ] **Step 4: Write the request and controller, and add the route**

`app/Http/Requests/LibraryImportRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LibraryImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'max:500'],
            'items.*.imdb_id' => ['required', 'string', 'regex:/^tt\d{7,8}$/'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.poster' => ['nullable', 'string', 'max:500'],
            'items.*.year' => ['nullable', 'string', 'max:10'],
            'items.*.status' => ['required', Rule::enum(LibraryStatus::class)],
            'items.*.reaction' => ['nullable', Rule::enum(LibraryReaction::class), 'prohibited_unless:items.*.status,watched'],
            'items.*.watched_at' => ['nullable', 'date'],
        ];
    }
}
```

`app/Http/Controllers/LibraryImportController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\MergeGuestLibrary;
use App\Http\Requests\LibraryImportRequest;
use App\Http\Resources\LibraryItemResource;
use App\Models\LibraryItem;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class LibraryImportController extends Controller
{
    public function store(LibraryImportRequest $request, MergeGuestLibrary $mergeGuestLibrary): AnonymousResourceCollection
    {
        /** @var array{items: array<int, array{imdb_id: string, title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null, watched_at?: string|null}>} $validated */
        $validated = $request->validated();

        $mergeGuestLibrary->handle($request->user(), $validated['items']);

        return LibraryItemResource::collection(LibraryItem::query()->forUser($request->user())->latest()->get());
    }
}
```

In `routes/web.php`, add `use App\Http\Controllers\LibraryImportController;`, and inside the `auth` group, before the `PUT /library/{imdbId}` route:

```php
    Route::post('/library/import', [LibraryImportController::class, 'store'])->name('library.import.store');
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --compact --filter=LibraryImportControllerTest`
Expected: PASS.

If the `items.0.reaction` validation test fails because `prohibited_unless` does not resolve the `*` wildcard in your Laravel version, replace that rule with a closure rule. The closure reads `$this->input(str_replace('.reaction', '.status', $attribute))` and fails when it is not `watched` and the value is not null.

- [ ] **Step 6: Lint and commit**

```bash
vendor/bin/pint --dirty --format agent
composer lint && composer test
git add app/Actions/MergeGuestLibrary.php app/Http/Requests/LibraryImportRequest.php app/Http/Controllers/LibraryImportController.php routes/web.php tests/Feature/LibraryImportControllerTest.php
git commit -m "feat: merge a guest library into the account on sign-in

Refs #22"
```

---

### Task 4: Client library state rules (pure, Node-tested)

**Files:**
- Create: `resources/js/Composables/libraryState.js`
- Create: `tests/js/libraryState.test.mjs`
- Modify: `package.json` (`scripts`)

**Interfaces:**
- Consumes: nothing (it must not import Vue, Inertia or any browser API, so Node can run it).
- Produces (all exported from `libraryState.js`):
  - `STATUS = {TO_WATCH: 'to_watch', WATCHED: 'watched'}` and `REACTIONS = ['liked', 'disliked']`.
  - `toLibraryItem(movie, now = new Date())`, which returns `{imdb_id, title, poster, year, status: 'to_watch', reaction: null, watched_at: null, saved_at: ISO}`.
  - `upgradeLegacyItem(row)`, which maps a version 1 watchlist row (numeric or missing `saved_at`) to a `to_watch` library item.
  - `applyLibraryChange(item, change, now = new Date())`, where `change` is `{type: 'add-to-watchlist'}`, `{type: 'mark-watched', reaction?: 'liked'|'disliked'|null}` or `{type: 'set-reaction', reaction: 'liked'|'disliked'|null}`. It returns a new item and throws `Error('A reaction needs the title to be watched')` for `set-reaction` on an unwatched item.
  - `splitLibrary(items)`, which returns `{toWatch, watched}`: `toWatch` sorted by `saved_at` and `watched` by `watched_at`, both newest first.

- [ ] **Step 1: Write the failing tests**

`tests/js/libraryState.test.mjs`:

```js
import {test} from 'node:test';
import assert from 'node:assert/strict';
import {
    applyLibraryChange,
    splitLibrary,
    toLibraryItem,
    upgradeLegacyItem,
} from '../../resources/js/Composables/libraryState.js';

const now = new Date('2026-10-15T12:00:00.000Z');
const movie = {imdb_id: 'tt1375666', title: 'Inception', poster: 'p.jpg', year: 2010};

test('a new item starts on the watchlist', () => {
    assert.deepEqual(toLibraryItem(movie, now), {
        imdb_id: 'tt1375666',
        title: 'Inception',
        poster: 'p.jpg',
        year: '2010',
        status: 'to_watch',
        reaction: null,
        watched_at: null,
        saved_at: '2026-10-15T12:00:00.000Z',
    });
});

test('legacy rows with numeric saved_at become to watch items', () => {
    const upgraded = upgradeLegacyItem({imdb_id: 'tt1', title: 'A', poster: null, year: '1999', saved_at: 1_700_000_000_000});
    assert.equal(upgraded.status, 'to_watch');
    assert.equal(upgraded.reaction, null);
    assert.equal(upgraded.watched_at, null);
    assert.equal(upgraded.saved_at, new Date(1_700_000_000_000).toISOString());
});

test('legacy rows without saved_at still get a valid date', () => {
    assert.equal(upgradeLegacyItem({imdb_id: 'tt1', title: 'A'}).saved_at, new Date(0).toISOString());
});

test('marking watched stamps watched_at and keeps a given reaction', () => {
    const watched = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched', reaction: 'liked'}, now);
    assert.equal(watched.status, 'watched');
    assert.equal(watched.reaction, 'liked');
    assert.equal(watched.watched_at, now.toISOString());
});

test('marking an already watched title keeps its watched date', () => {
    const first = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched'}, now);
    const later = applyLibraryChange(first, {type: 'mark-watched', reaction: 'disliked'}, new Date('2026-12-01T00:00:00Z'));
    assert.equal(later.watched_at, now.toISOString());
    assert.equal(later.reaction, 'disliked');
});

test('setting a reaction requires the title to be watched', () => {
    assert.throws(
        () => applyLibraryChange(toLibraryItem(movie, now), {type: 'set-reaction', reaction: 'liked'}, now),
        /A reaction needs the title to be watched/,
    );
});

test('unknown reactions are refused', () => {
    const watched = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched'}, now);
    assert.throws(() => applyLibraryChange(watched, {type: 'set-reaction', reaction: 'meh'}, now), /Unknown reaction/);
});

test('going back to the watchlist clears reaction and watched date', () => {
    const watched = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched', reaction: 'liked'}, now);
    const back = applyLibraryChange(watched, {type: 'add-to-watchlist'}, now);
    assert.equal(back.status, 'to_watch');
    assert.equal(back.reaction, null);
    assert.equal(back.watched_at, null);
});

test('changes never mutate the original item', () => {
    const item = toLibraryItem(movie, now);
    applyLibraryChange(item, {type: 'mark-watched'}, now);
    assert.equal(item.status, 'to_watch');
});

test('the library splits into tabs, newest first', () => {
    const a = {...toLibraryItem({...movie, imdb_id: 'tt1'}, new Date('2026-01-01T00:00:00Z'))};
    const b = {...toLibraryItem({...movie, imdb_id: 'tt2'}, new Date('2026-02-01T00:00:00Z'))};
    const c = applyLibraryChange(toLibraryItem({...movie, imdb_id: 'tt3'}, now), {type: 'mark-watched'}, new Date('2026-03-01T00:00:00Z'));
    const d = applyLibraryChange(toLibraryItem({...movie, imdb_id: 'tt4'}, now), {type: 'mark-watched'}, new Date('2026-04-01T00:00:00Z'));

    const {toWatch, watched} = splitLibrary([a, c, b, d]);
    assert.deepEqual(toWatch.map((item) => item.imdb_id), ['tt2', 'tt1']);
    assert.deepEqual(watched.map((item) => item.imdb_id), ['tt4', 'tt3']);
});
```

In `package.json`, add to `scripts`:

```json
"test:js": "node --test tests/js/"
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `npm run test:js`
Expected: FAIL, because `libraryState.js` cannot be found.

- [ ] **Step 3: Implement the module**

`resources/js/Composables/libraryState.js`:

```js
/**
 * Library state rules shared by the client: the same rules the server applies in
 * App\Actions\UpdateLibraryItem. Pure functions only, so they can be tested with Node.
 */

export const STATUS = Object.freeze({TO_WATCH: 'to_watch', WATCHED: 'watched'});
export const REACTIONS = Object.freeze(['liked', 'disliked']);

const toIsoString = (date) => date.toISOString();

export function toLibraryItem(movie, now = new Date()) {
    return {
        imdb_id: movie.imdb_id,
        title: movie.title,
        poster: movie.poster ?? null,
        year: movie.year != null ? String(movie.year) : null,
        status: STATUS.TO_WATCH,
        reaction: null,
        watched_at: null,
        saved_at: toIsoString(now),
    };
}

/**
 * Maps a row from the version 1 "watchlist" store (or the older localStorage list) to a library item.
 */
export function upgradeLegacyItem(row) {
    const savedAt = typeof row.saved_at === 'number'
        ? new Date(row.saved_at)
        : new Date(row.saved_at ?? 0);

    return {
        ...toLibraryItem(row, Number.isNaN(savedAt.getTime()) ? new Date(0) : savedAt),
    };
}

function assertReaction(reaction) {
    if (reaction !== null && !REACTIONS.includes(reaction)) {
        throw new Error(`Unknown reaction "${reaction}"`);
    }
}

export function applyLibraryChange(item, change, now = new Date()) {
    switch (change.type) {
        case 'add-to-watchlist':
            return {...item, status: STATUS.TO_WATCH, reaction: null, watched_at: null};

        case 'mark-watched': {
            const reaction = change.reaction ?? null;
            assertReaction(reaction);
            const isAlreadyWatched = item.status === STATUS.WATCHED && item.watched_at;
            return {
                ...item,
                status: STATUS.WATCHED,
                reaction,
                watched_at: isAlreadyWatched ? item.watched_at : toIsoString(now),
            };
        }

        case 'set-reaction': {
            if (item.status !== STATUS.WATCHED) {
                throw new Error('A reaction needs the title to be watched');
            }
            const reaction = change.reaction ?? null;
            assertReaction(reaction);
            return {...item, reaction};
        }

        default:
            throw new Error(`Unknown library change "${change.type}"`);
    }
}

const newestFirst = (key) => (a, b) => Date.parse(b[key] ?? 0) - Date.parse(a[key] ?? 0);

export function splitLibrary(items) {
    return {
        toWatch: items.filter((item) => item.status === STATUS.TO_WATCH).sort(newestFirst('saved_at')),
        watched: items.filter((item) => item.status === STATUS.WATCHED).sort(newestFirst('watched_at')),
    };
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `npm run test:js`
Expected: PASS (10 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/js/Composables/libraryState.js tests/js/libraryState.test.mjs package.json
git commit -m "feat: add the client library state rules with node tests

Refs #22"
```

---

### Task 5: `useLibrary` composable (guests, members, merge, offline hooks)

**Files:**
- Create: `resources/js/Composables/useLibrary.js`
- Modify: `resources/js/Composables/useWatchlist.js` (replace the whole file)
- Modify: `resources/js/app.js:10` (the `requestPersistentStorage` import)

**Interfaces:**
- Consumes: from Task 4, `STATUS`, `toLibraryItem`, `upgradeLegacyItem`, `applyLibraryChange` and `splitLibrary`; from Tasks 2 and 3, the routes `library.index`, `library.update`, `library.destroy` and `library.import.store` and the `LibraryItemResource` shape.
- Produces:
  - `useLibrary()` returns `{library, toWatch, watched, isLibraryLoaded, isInWatchlist(imdbId), isWatched(imdbId), reactionFor(imdbId), addToWatchlist(movie), markWatched(movie, reaction = null), setReaction(imdbId, reaction), unmarkWatched(imdbId), remove(imdbId)}`. `library`, `toWatch`, `watched` and `isLibraryLoaded` are refs or computeds, and every action returns a `Promise<void>`.
  - `requestPersistentStorage()` is exported from `useLibrary.js`.
  - `useWatchlist()` still returns `{watchlist, isWatchlistLoaded, isMovieInWatchlist, toggleMovie, removeMovie}`.
  - Service worker messages: `{type: 'CACHE_LIBRARY', items: [{imdb_id, poster}], reset}` and `{type: 'UNCACHE_LIBRARY_TITLE', imdbId}` (handled in Task 6).

- [ ] **Step 1: Write `useLibrary.js`**

```js
import {computed, ref, watch} from 'vue';
import {usePage} from '@inertiajs/vue3';
import {applyLibraryChange, splitLibrary, STATUS, toLibraryItem, upgradeLegacyItem} from '@/Composables/libraryState.js';

/**
 * One library per person: titles to watch and titles already watched (optionally liked or disliked).
 * Guests keep it on the device in IndexedDB; members keep it in their account, and a guest library
 * is merged into the account on sign-in. Saved titles are cached by the service worker for offline use.
 */

const LEGACY_STORAGE_KEY = 'movie-guru-watchlist';
const LIBRARY_OWNER_KEY = 'movie-guru-watchlist-owner';
const DB_NAME = 'movie-guru';
const DB_VERSION = 2;
const LEGACY_STORE = 'watchlist';
const STORE_NAME = 'library';

const library = ref([]);
const isLibraryLoaded = ref(false);
const libraryTabs = computed(() => splitLibrary(library.value));

let loadedForUserId;
let loadingPromise = null;

/**
 * Version 2 replaces the version 1 "watchlist" store with "library", copying every saved
 * title as "to_watch" inside the upgrade transaction so nothing is lost.
 */
function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = () => {
            const db = request.result;
            const libraryStore = db.objectStoreNames.contains(STORE_NAME)
                ? request.transaction.objectStore(STORE_NAME)
                : db.createObjectStore(STORE_NAME, {keyPath: 'imdb_id'});

            if (db.objectStoreNames.contains(LEGACY_STORE)) {
                const legacyRows = request.transaction.objectStore(LEGACY_STORE).getAll();
                legacyRows.onsuccess = () => {
                    legacyRows.result.forEach((row) => libraryStore.put(upgradeLegacyItem(row)));
                    db.deleteObjectStore(LEGACY_STORE);
                };
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function runTransaction(mode, operation) {
    const db = await openDatabase();
    try {
        return await new Promise((resolve, reject) => {
            const transaction = db.transaction(STORE_NAME, mode);
            const request = operation(transaction.objectStore(STORE_NAME));
            transaction.oncomplete = () => resolve(request?.result);
            transaction.onerror = () => reject(transaction.error);
            transaction.onabort = () => reject(transaction.error);
        });
    } finally {
        db.close();
    }
}

const readGuestItems = () => runTransaction('readonly', (store) => store.getAll());
const saveGuestItem = (item) => runTransaction('readwrite', (store) => store.put(item));
const deleteGuestItem = (imdbId) => runTransaction('readwrite', (store) => store.delete(imdbId));
const clearGuestItems = () => runTransaction('readwrite', (store) => store.clear());

/**
 * Moves a watchlist saved by a much older version of the app from localStorage into IndexedDB, once.
 */
async function migrateLegacyLocalStorage() {
    let legacyItems;
    try {
        legacyItems = JSON.parse(localStorage.getItem(LEGACY_STORAGE_KEY) ?? '[]');
    } catch {
        legacyItems = [];
    }

    if (Array.isArray(legacyItems) && legacyItems.length > 0) {
        const savedAt = Date.now();
        await runTransaction('readwrite', (store) => {
            legacyItems.forEach((movie, index) => store.put(upgradeLegacyItem({...movie, saved_at: savedAt - index})));
        });
    }

    localStorage.removeItem(LEGACY_STORAGE_KEY);
}

async function loadGuestLibrary() {
    await migrateLegacyLocalStorage();
    return readGuestItems();
}

function xsrfHeaders() {
    const token = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
        ?.slice('XSRF-TOKEN='.length);

    return token ? {'X-XSRF-TOKEN': decodeURIComponent(token)} : {};
}

async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...xsrfHeaders(),
            ...options.headers,
        },
    });

    if (!response.ok) {
        throw new Error(`Library request failed with status ${response.status}`);
    }

    return response.status === 204 ? null : response.json();
}

async function loadMemberLibrary() {
    const guestItems = await loadGuestLibrary();

    if (guestItems.length === 0) {
        const {data} = await requestJson(route('library.index'));
        return data;
    }

    const {data} = await requestJson(route('library.import.store'), {
        method: 'POST',
        body: JSON.stringify({items: guestItems.map(({saved_at, ...item}) => item)}),
    });
    await clearGuestItems();
    return data;
}

function loadLibrary(userId) {
    if (loadedForUserId === userId && loadingPromise) {
        return loadingPromise;
    }

    loadedForUserId = userId;
    loadingPromise = (userId ? loadMemberLibrary() : loadGuestLibrary())
        .then((items) => {
            if (loadedForUserId === userId) {
                library.value = items;
                isLibraryLoaded.value = true;
                cacheForOffline(items, {reset: hasLibraryOwnerChanged(userId)});
            }
        })
        .catch((error) => {
            console.error('Loading the library failed', error);
            loadingPromise = null;
        });

    return loadingPromise;
}

function postToServiceWorker(message) {
    navigator.serviceWorker?.controller?.postMessage(message);
}

/**
 * Cached offline pages embed the signed-in user, so they are rebuilt when a different person
 * (or a guest) uses the app on this device. Remembered across page loads.
 */
function hasLibraryOwnerChanged(userId) {
    const owner = userId ? `user:${userId}` : 'guest';
    try {
        const previousOwner = localStorage.getItem(LIBRARY_OWNER_KEY);
        localStorage.setItem(LIBRARY_OWNER_KEY, owner);
        return previousOwner !== null && previousOwner !== owner;
    } catch {
        return true;
    }
}

function cacheForOffline(items, {reset = false} = {}) {
    if (items.length === 0 && !reset) {
        return;
    }

    postToServiceWorker({
        type: 'CACHE_LIBRARY',
        items: items.map(({imdb_id, poster}) => ({imdb_id, poster})),
        reset,
    });
}

/**
 * Asks the browser not to evict cached titles and the on-device library under storage pressure.
 */
export function requestPersistentStorage() {
    if (!navigator.storage?.persist) {
        return;
    }

    navigator.storage.persisted()
        .then((isPersisted) => isPersisted || navigator.storage.persist())
        .catch((error) => console.error('Requesting persistent storage failed', error));
}

function showToast(message) {
    new BroadcastChannel('toast-notifications').postMessage({message, level: 'danger'});
}

const findItem = (imdbId) => library.value.find((item) => item.imdb_id === imdbId) ?? null;

export function useLibrary() {
    const page = usePage();

    watch(
        () => page.props.auth?.user?.id ?? null,
        (userId) => loadLibrary(userId),
        {immediate: true},
    );

    const isMemberSignedIn = () => Boolean(page.props.auth?.user);

    const persist = async (item) => {
        if (!isMemberSignedIn()) {
            await saveGuestItem(item);
            return;
        }

        await requestJson(route('library.update', {imdbId: item.imdb_id}), {
            method: 'PUT',
            body: JSON.stringify({
                title: item.title,
                poster: item.poster,
                year: item.year,
                status: item.status,
                reaction: item.reaction,
            }),
        });
    };

    const destroy = async (imdbId) => {
        if (isMemberSignedIn()) {
            await requestJson(route('library.destroy', {imdbId}), {method: 'DELETE'});
        } else {
            await deleteGuestItem(imdbId);
        }
    };

    /**
     * Applies a change optimistically, saves it, and restores this action's snapshot on failure.
     */
    const commit = async (nextItem, failureMessage) => {
        const snapshot = library.value;
        library.value = [nextItem, ...snapshot.filter((item) => item.imdb_id !== nextItem.imdb_id)];

        try {
            await persist(nextItem);
        } catch (error) {
            console.error('Saving the library failed', error);
            library.value = snapshot;
            showToast(navigator.onLine ? failureMessage : "Couldn't sync, try again when you're online.");
            return false;
        }

        requestPersistentStorage();
        cacheForOffline([nextItem]);
        return true;
    };

    const addToWatchlist = async (movie) => {
        const existing = findItem(movie.imdb_id);
        const next = existing
            ? applyLibraryChange(existing, {type: 'add-to-watchlist'})
            : toLibraryItem(movie);
        await commit(next, `Couldn't save "${next.title}" to your watchlist. Please try again.`);
    };

    const markWatched = async (movie, reaction = null) => {
        const existing = findItem(movie.imdb_id) ?? toLibraryItem(movie);
        await commit(
            applyLibraryChange(existing, {type: 'mark-watched', reaction}),
            `Couldn't mark "${existing.title}" as watched. Please try again.`,
        );
    };

    const setReaction = async (imdbId, reaction) => {
        const existing = findItem(imdbId);
        if (!existing || existing.status !== STATUS.WATCHED) {
            return;
        }
        await commit(
            applyLibraryChange(existing, {type: 'set-reaction', reaction}),
            "Couldn't save your reaction. Please try again.",
        );
    };

    const remove = async (imdbId) => {
        const snapshot = library.value;
        library.value = snapshot.filter((item) => item.imdb_id !== imdbId);

        try {
            await destroy(imdbId);
        } catch (error) {
            console.error('Removing from the library failed', error);
            library.value = snapshot;
            showToast(navigator.onLine
                ? "Couldn't remove that title. Please try again."
                : "Couldn't sync, try again when you're online.");
            return;
        }

        postToServiceWorker({type: 'UNCACHE_LIBRARY_TITLE', imdbId});
    };

    return {
        library,
        toWatch: computed(() => libraryTabs.value.toWatch),
        watched: computed(() => libraryTabs.value.watched),
        isLibraryLoaded,
        isInWatchlist: (imdbId) => findItem(imdbId)?.status === STATUS.TO_WATCH,
        isWatched: (imdbId) => findItem(imdbId)?.status === STATUS.WATCHED,
        reactionFor: (imdbId) => findItem(imdbId)?.reaction ?? null,
        addToWatchlist,
        markWatched,
        setReaction,
        unmarkWatched: remove,
        remove,
    };
}
```

- [ ] **Step 2: Replace `useWatchlist.js` with the wrapper**

```js
import {useLibrary} from '@/Composables/useLibrary.js';

export {requestPersistentStorage} from '@/Composables/useLibrary.js';

/**
 * The "to watch" side of the library, kept for the header count and WatchlistButton.
 */
export function useWatchlist() {
    const {toWatch, isLibraryLoaded, isInWatchlist, addToWatchlist, remove} = useLibrary();

    return {
        watchlist: toWatch,
        isWatchlistLoaded: isLibraryLoaded,
        isMovieInWatchlist: isInWatchlist,
        toggleMovie: (movie) => (isInWatchlist(movie.imdb_id) ? remove(movie.imdb_id) : addToWatchlist(movie)),
        removeMovie: remove,
    };
}
```

In `resources/js/app.js`, change the import to `import {requestPersistentStorage} from '@/Composables/useLibrary.js';`.

- [ ] **Step 3: Build and run the checks**

Run: `npm run build && npm run test:js && php artisan test --compact`
Expected: the build succeeds, and all JS and PHP tests pass.

- [ ] **Step 4: Check the IndexedDB upgrade in headless Chrome**

Create a scratch page outside the repo, in your scratchpad, that:
1. Opens `movie-guru` at version 1 with a `watchlist` store and puts two rows, one with a numeric `saved_at`.
2. Imports the built `useLibrary` upgrade logic by copying `openDatabase` and `upgradeLegacyItem`, and opens the database at version 2.
3. Writes `JSON.stringify(rows from the library store)` into `document.title`.

Run it with `"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --dump-dom http://localhost:<port>/page.html`. The page must be served over http by a scratch `python3 -m http.server`, not opened as a file URL. Expected: both rows appear with `status: "to_watch"` and an ISO `saved_at`. Delete the scratch files afterwards; nothing from this step is committed.

- [ ] **Step 5: Commit**

```bash
git add resources/js/Composables/useLibrary.js resources/js/Composables/useWatchlist.js resources/js/app.js
git commit -m "feat: keep watched titles in one library for guests and members

Refs #22"
```

---

### Task 6: Service worker: library endpoint and offline caching

**Files:**
- Modify: `public/sw.js` (cache version constants at lines 5-10, the `/my-watchlist` route around line 816, and the message handlers around lines 846-851)

**Interfaces:**
- Consumes: the `CACHE_LIBRARY` and `UNCACHE_LIBRARY_TITLE` messages from Task 5, and `GET /library` from Task 2.
- Produces: `GET /library` is served network-first from `WATCHLIST_CACHE`, and both tabs' titles are cached for offline use.

- [ ] **Step 1: Point the network-first route at `/library`**

Replace:

```js
    } else if (isSameOrigin && url.pathname === '/my-watchlist' && request.method === 'GET') {
        // A member's watchlist changes on every save, so always ask the network first.
```

with:

```js
    } else if (isSameOrigin && url.pathname === '/library' && request.method === 'GET') {
        // A member's library changes on every save, so always ask the network first.
```

- [ ] **Step 2: Rename the messages**

Replace `event.data?.type === 'CACHE_WATCHLIST'` with `event.data?.type === 'CACHE_LIBRARY'`, and `event.data?.type === 'UNCACHE_WATCHLIST_TITLE'` with `event.data?.type === 'UNCACHE_LIBRARY_TITLE'`. The handler functions (`cacheWatchlistTitles` and `uncacheWatchlistTitle`) stay as they are; they already cache every item they are given, which now includes watched titles.

- [ ] **Step 3: Bump the cache versions**

Change every `4.17.0` in the cache name constants to `4.18.0`, leaving the unversioned `WATCHLIST_CACHE` as it is. Activate then purges stale cached library pages, as built in the earlier fix.

- [ ] **Step 4: Check syntax and build**

Run: `node --check public/sw.js && grep -n "my-watchlist\|CACHE_WATCHLIST\|UNCACHE_WATCHLIST" public/sw.js`
Expected: the syntax check passes, and the grep prints nothing.

- [ ] **Step 5: Commit**

```bash
git add public/sw.js
git commit -m "feat: cache the whole library for offline use

Refs #22"
```

---

### Task 7: `WatchedButton` on cards and the detail view

**Files:**
- Create: `resources/js/Components/WatchedButton.vue`
- Modify: `resources/js/Components/SearchCard.vue:108-112` (next to `WatchlistButton`) and its imports at line 144
- Modify: `resources/js/Components/DetailCard.vue:18-20` (next to `WatchlistButton`) and its imports at line 429

**Interfaces:**
- Consumes: `useLibrary()` from Task 5 (`isWatched`, `reactionFor`, `markWatched`, `setReaction`, `unmarkWatched`).
- Produces: the `<WatchedButton :movie="{imdb_id, title, poster, year}" />` component. It accepts extra classes through `class`, like `WatchlistButton`.

- [ ] **Step 1: Write the component**

`resources/js/Components/WatchedButton.vue`:

```vue
<script setup>
import {computed, onBeforeUnmount, ref, useId} from 'vue';
import {useLibrary} from '@/Composables/useLibrary.js';

// Two root nodes (the button and its popover): parent classes go on the button.
defineOptions({inheritAttrs: false});

const props = defineProps({
    movie: {
        type: Object,
        required: true,
    },
});

const REACTION_PROMPT_MS = 5000;

const {isWatched, reactionFor, markWatched, setReaction, unmarkWatched} = useLibrary();

const popoverId = `watched-${useId()}`;
const anchorName = `--watched-${useId()}`;
const button = ref(null);
const popover = ref(null);
let hidePromptTimer = null;

const watched = computed(() => isWatched(props.movie.imdb_id));
const reaction = computed(() => reactionFor(props.movie.imdb_id));
const reactionEmoji = computed(() => ({liked: '👍', disliked: '👎'})[reaction.value] ?? '');

const openReactionPrompt = () => {
    try {
        popover.value?.showPopover({source: button.value});
    } catch {
        popover.value?.showPopover();
    }

    clearTimeout(hidePromptTimer);
    hidePromptTimer = setTimeout(() => {
        if (popover.value && !popover.value.matches(':hover, :focus-within')) {
            popover.value.hidePopover();
        }
    }, REACTION_PROMPT_MS);
};

const onButtonClick = async () => {
    if (watched.value) {
        popover.value?.togglePopover();
        return;
    }

    await markWatched(props.movie);
    if (watched.value) {
        openReactionPrompt();
    }
};

const chooseReaction = async (choice) => {
    await setReaction(props.movie.imdb_id, reaction.value === choice ? null : choice);
    popover.value?.hidePopover();
};

const markNotWatched = async () => {
    popover.value?.hidePopover();
    await unmarkWatched(props.movie.imdb_id);
};

onBeforeUnmount(() => clearTimeout(hidePromptTimer));
</script>

<template>
    <button
        ref="button"
        v-bind="$attrs"
        :aria-label="watched ? `${movie.title}: watched${reaction ? `, ${reaction}` : ''}. Change` : `Mark ${movie.title} as watched`"
        :aria-pressed="watched"
        :class="watched ? 'watched-btn--on bg-emerald-600/85 hover:bg-emerald-600' : 'bg-white/20 hover:bg-white/40'"
        :style="{anchorName}"
        class="watched-btn squircle flex items-center gap-1 backdrop-blur-md text-white text-xs font-medium px-2.5 py-0.5 rounded-sm transition-all active:scale-90"
        type="button"
        @click.stop="onButtonClick"
    >
        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
        <span class="text-box-tight watched-btn__label">{{ watched ? `Watched ${reactionEmoji}`.trim() : 'Watched?' }}</span>
    </button>

    <div
        :id="popoverId"
        ref="popover"
        :style="{positionAnchor: anchorName}"
        class="watched-popover m-0 rounded-xl border border-gray-200 bg-white p-2 text-gray-900 shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        popover="auto"
        role="dialog"
        :aria-label="`Did you like ${movie.title}?`"
        @click.stop
    >
        <p class="px-1 pb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400">Did you like it?</p>
        <div class="flex gap-1">
            <button
                :aria-label="`Like ${movie.title}`"
                :aria-pressed="reaction === 'liked'"
                :class="reaction === 'liked' ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                class="rounded-lg px-3 py-1.5 text-lg transition-colors"
                type="button"
                @click="chooseReaction('liked')"
            >👍</button>
            <button
                :aria-label="`Dislike ${movie.title}`"
                :aria-pressed="reaction === 'disliked'"
                :class="reaction === 'disliked' ? 'bg-rose-100 dark:bg-rose-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                class="rounded-lg px-3 py-1.5 text-lg transition-colors"
                type="button"
                @click="chooseReaction('disliked')"
            >👎</button>
        </div>
        <button
            class="mt-1.5 w-full rounded-lg px-2 py-1.5 text-left text-xs font-medium text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
            type="button"
            @click="markNotWatched"
        >Not watched</button>
    </div>
</template>

<style scoped>
@container style(--card-density: compact) {
    .watched-btn__label {
        display: none;
    }
}

.watched-popover {
    opacity: 0;
    transform: scale(0.95) translateY(-4px);
    transition: opacity 0.15s ease-out, transform 0.15s ease-out, display 0.15s allow-discrete, overlay 0.15s allow-discrete;
}

.watched-popover:popover-open {
    opacity: 1;
    transform: none;
}

@starting-style {
    .watched-popover:popover-open {
        opacity: 0;
        transform: scale(0.95) translateY(-4px);
    }
}

/* Tethered under the button where anchor positioning exists; centred by the UA otherwise. */
@supports (position-area: block-end) {
    .watched-popover {
        inset: auto;
        margin-block-start: 0.375rem;
        position-area: block-end span-inline-start;
        position-try-fallbacks: flip-block, flip-inline;
    }
}

@media (prefers-reduced-motion: reduce) {
    .watched-popover {
        transition: none;
    }
}
</style>
```

- [ ] **Step 2: Place it on cards and the detail view**

In `SearchCard.vue`, import `WatchedButton from "@/Components/WatchedButton.vue"` next to the `WatchlistButton` import. Add it immediately after the `<WatchlistButton ... />` element in the action row, with the same class string, so both buttons match:

```vue
            <WatchedButton
                :movie="movie"
                class="flex-none !bg-gray-100 dark:!bg-gray-800 hover:!bg-gray-200 dark:hover:!bg-gray-700 !text-gray-900 dark:!text-white !text-xs !font-bold !py-2.5 !px-3 !rounded-xl !transition-all !duration-200"
            />
```

In `DetailCard.vue`, import it the same way and add it right after the `<WatchlistButton ... />` in the poster overlay:

```vue
                            <WatchedButton
                                :movie="{ imdb_id: detail.imdbID, title: detail.Title, year: detail.Year, poster: detail.Poster }"/>
```

- [ ] **Step 3: Build and check visually**

Run: `npm run build`
Expected: success, with no warnings.

Then render a scratch page in headless Chrome at 390px and 1280px wide, in light and dark mode. The page mounts `WatchedButton` in both states (not watched, and watched and liked), with the popover open via `showPopover()`. Check that:
- The labels never wrap.
- The popover sits under the button and is not clipped by the card's `overflow-hidden` (it is in the top layer).
- Focus outlines are visible.

Fix anything that looks off before committing.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/WatchedButton.vue resources/js/Components/SearchCard.vue resources/js/Components/DetailCard.vue
git commit -m "feat: mark titles watched with an optional like or dislike

Refs #22"
```

---

### Task 8: Watchlist page with "To watch" and "Watched" tabs

**Files:**
- Modify: `resources/js/Pages/Watchlist.vue` (replace the whole file)

**Interfaces:**
- Consumes: `useLibrary()` from Task 5, `WatchedButton` from Task 7, and the existing route `watchlist` (`/watchlist`).
- Produces: `/watchlist` and `/watchlist?tab=watched`. Any other `tab` value shows "To watch".

- [ ] **Step 1: Rewrite the page**

```vue
<script setup>
import {computed} from 'vue';
import {Head, Link, usePage} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import WatchedButton from '@/Components/WatchedButton.vue';
import {useLibrary} from '@/Composables/useLibrary.js';

defineOptions({layout: BaseLayout});

const {toWatch, watched, isLibraryLoaded, remove} = useLibrary();

const page = usePage();

const activeTab = computed(() => {
    const tab = new URL(page.url, window.location.origin).searchParams.get('tab');
    return tab === 'watched' ? 'watched' : 'to-watch';
});

const tabs = computed(() => [
    {key: 'to-watch', label: 'To watch', count: toWatch.value.length, href: route('watchlist')},
    {key: 'watched', label: 'Watched', count: watched.value.length, href: route('watchlist', {tab: 'watched'})},
]);

const items = computed(() => (activeTab.value === 'watched' ? watched.value : toWatch.value));

const storageDescription = computed(() => page.props.auth?.user
    ? 'Synced to your account and available offline.'
    : 'Saved on this device and available offline. Sign in to keep them in your account.');

const emptyState = computed(() => (activeTab.value === 'watched'
    ? {title: 'Nothing watched yet', body: 'Mark titles as watched from any movie card.'}
    : {title: 'Your watchlist is empty', body: 'Browse movies and click the Watchlist button to save them here.'}));

const moviePoster = (movie) => (movie.poster && movie.poster !== 'N/A' ? movie.poster : '/assets/images/no-poster.jpg');
</script>

<template>
    <Head>
        <title>{{ activeTab === 'watched' ? 'Watched' : 'My Watchlist' }} - Movie Guru</title>
        <meta content="Movies you want to watch and movies you've watched, available offline." head-key="description" name="description"/>
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900 py-12 px-4">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🎬 My Watchlist</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">{{ storageDescription }}</p>
            </div>

            <nav aria-label="Watchlist sections" class="mb-8 flex gap-1 border-b border-gray-200 dark:border-gray-800">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :aria-current="activeTab === tab.key ? 'page' : undefined"
                    :class="activeTab === tab.key
                        ? 'border-primary-600 text-primary-700 dark:border-primary-400 dark:text-primary-300'
                        : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                    :href="tab.href"
                    class="-mb-px border-b-2 px-4 py-2.5 text-sm font-semibold transition-colors"
                    preserve-scroll
                >
                    {{ tab.label }}
                    <span class="ml-1 tabular-nums text-xs font-medium opacity-70">({{ tab.count }})</span>
                </Link>
            </nav>

            <div v-if="!isLibraryLoaded" aria-busy="true" aria-label="Loading your watchlist"
                 class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <div v-for="placeholder in 6" :key="placeholder"
                     class="animate-pulse rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <div class="aspect-2/3 bg-gray-200 dark:bg-gray-700"/>
                    <div class="p-2 space-y-2">
                        <div class="h-3 rounded bg-gray-200 dark:bg-gray-700 w-3/4"/>
                        <div class="h-2.5 rounded bg-gray-200 dark:bg-gray-700 w-1/3"/>
                    </div>
                </div>
            </div>

            <div v-else-if="items.length === 0" class="text-center py-24">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">{{ emptyState.title }}</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">{{ emptyState.body }}</p>
                <Link :href="route('home')"
                      class="mt-6 inline-block px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white font-semibold rounded-xl transition-colors">
                    Browse Movies
                </Link>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <div
                    v-for="movie in items"
                    :key="movie.imdb_id"
                    class="group relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 shadow hover:shadow-lg transition-all duration-300 hover:-translate-y-1 cv-card-compact"
                >
                    <Link :href="route('movie.detail.full', {imdbID: movie.imdb_id})" prefetch="click">
                        <div class="aspect-2/3 overflow-hidden">
                            <img :alt="movie.title + ' Poster'" :src="moviePoster(movie)"
                                 class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                                 loading="lazy"/>
                        </div>
                        <div class="p-2">
                            <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 leading-tight">{{ movie.title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ movie.year }}</p>
                        </div>
                    </Link>

                    <div class="absolute top-2 left-2">
                        <WatchedButton :movie="movie"/>
                    </div>

                    <button
                        v-if="activeTab === 'to-watch'"
                        :aria-label="`Remove ${movie.title} from Watchlist`"
                        class="absolute top-2 right-2 p-1.5 bg-red-500/80 hover:bg-red-600 text-white rounded-full transition-all active:scale-90 focus-visible:opacity-100 [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover:opacity-100"
                        title="Remove from Watchlist"
                        @click.prevent="remove(movie.imdb_id)"
                    >
                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
```

- [ ] **Step 2: Build and check visually**

Run: `npm run build`
Expected: success, with no warnings.

Render `/watchlist` and `/watchlist?tab=watched` from the built app in headless Chrome, at 390px and 1280px, light and dark. Seed a guest library through a scratch page script that writes two to-watch and two watched (one liked) items into IndexedDB version 2 before loading the page. Check that:
- The tab counts are correct, and the active tab underline lines up.
- Watched cards show the reaction emoji.
- The remove button only appears on "To watch".
- `?tab=garbage` shows "To watch".
- Each tab's empty state renders.

Fix anything off.

- [ ] **Step 3: Commit**

```bash
git add resources/js/Pages/Watchlist.vue
git commit -m "feat: show to watch and watched as tabs on the watchlist page

Refs #22"
```

---

### Task 9: Whole-branch verification

**Files:** none are created. Fix whatever the checks surface, in the file that owns it.

- [ ] **Step 1: Run every check**

```bash
composer lint
composer test
npm run test:js
npm run build
node --check public/sw.js
git diff main --stat | grep -c "—" || true
git grep -n "Watchlist::\|watchlist\.store\|watchlist\.destroy\|watchlist\.import\|my-watchlist" -- app routes resources tests public
```

Expected:
- All the commands succeed, and the build prints no warnings.
- The em dash count printed is `0`.
- The last grep only shows the `/my-watchlist` permanent redirect in `routes/web.php` and its test.

- [ ] **Step 2: Spec walkthrough**

Re-read `docs/superpowers/specs/2026-10-15-watched-list-design.md` section by section and tick each requirement against a commit. Anything missing gets a new task before calling the branch done.

- [ ] **Step 3: Report**

Summarize what changed in 2-3 sentences. List explicitly what was only checked in headless Chrome and what was never exercised in a real browser:
- the IndexedDB upgrade on a real device,
- sign-in merge end to end,
- offline tabs.

Do not push; ask first.
