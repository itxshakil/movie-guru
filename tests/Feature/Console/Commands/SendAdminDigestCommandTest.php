<?php

declare(strict_types=1);

use App\Mail\AdminDigestMail;
use App\Models\Contact;
use App\Models\LibraryItem;
use App\Models\MovieDetail;
use App\Models\NewsletterSubscription;
use App\Models\Search;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Point away from the real app.log so digest counts stay deterministic.
    // Deleted up front (rather than relying on cleanup) because the command's own
    // LogCommands calls write into this same path, which would otherwise leak
    // an ERROR line into the next test that reuses this path.
    $missingLogPath = storage_path('logs/admin-digest-test-missing.log');

    if (file_exists($missingLogPath)) {
        unlink($missingLogPath);
    }

    config([
        'mail.admin.address' => 'admin@example.com',
        'mail.admin.name' => 'Admin',
        'logging.channels.single.path' => $missingLogPath,
    ]);
});

it('sends weekly admin digest with counts and no top searches', function (): void {
    Mail::fake();
    Notification::fake();

    $user = User::factory()->create(['created_at' => now()->subDays(2)]);

    NewsletterSubscription::factory()->create(['created_at' => now()->subDays(2)]);
    Contact::factory()->create(['created_at' => now()->subDays(2)]);
    LibraryItem::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(2)]);
    MovieDetail::factory()->create(['created_at' => now()->subDays(2)]);

    $this->artisan('admin:digest', ['type' => 'weekly'])
        ->assertExitCode(Command::SUCCESS);

    Mail::assertQueued(
        AdminDigestMail::class,
        fn (AdminDigestMail $mail): bool => $mail->data->type === 'weekly'
            && $mail->data->newSignupsCount === 1
            && $mail->data->newsletterSubscribesCount === 1
            && $mail->data->contactSubmissionsCount === 1
            && $mail->data->watchlistAddsCount === 1
            && $mail->data->newMoviesCount === 1
            && $mail->data->errorLogCount === 0
            && $mail->data->topSearches === [],
    );
});

it('sends monthly admin digest with top searches populated', function (): void {
    Mail::fake();
    Notification::fake();

    Search::create(['query' => 'batman', 'total_results' => 50, 'search_count' => 3]);
    Search::create(['query' => 'inception', 'total_results' => 10, 'search_count' => 9]);

    $this->artisan('admin:digest', ['type' => 'monthly'])
        ->assertExitCode(Command::SUCCESS);

    Mail::assertQueued(
        AdminDigestMail::class,
        fn (AdminDigestMail $mail): bool => $mail->data->type === 'monthly'
            && count($mail->data->topSearches) === 2
            && $mail->data->topSearches[0]['query'] === 'batman',
    );
});

it('excludes activity from outside the digest period', function (): void {
    Mail::fake();
    Notification::fake();

    User::factory()->create(['created_at' => now()->subMonths(2)]);

    $this->artisan('admin:digest', ['type' => 'weekly'])
        ->assertExitCode(Command::SUCCESS);

    Mail::assertQueued(
        AdminDigestMail::class,
        fn (AdminDigestMail $mail): bool => $mail->data->newSignupsCount === 0,
    );
});

it('rejects an invalid digest type', function (): void {
    Mail::fake();

    $this->artisan('admin:digest', ['type' => 'daily'])
        ->assertExitCode(Command::INVALID);

    Mail::assertNothingQueued();
});

it('fails when the admin mail address is not configured', function (): void {
    Mail::fake();
    config(['mail.admin.address' => '']);

    $this->artisan('admin:digest', ['type' => 'weekly'])
        ->assertExitCode(Command::FAILURE);

    Mail::assertNothingQueued();
});

it('counts only error log lines within the digest period', function (): void {
    Mail::fake();
    Notification::fake();

    $path = storage_path('logs/admin-digest-test.log');

    $inWindow = now()->subHours(2)->format('Y-m-d H:i:s');
    $outOfWindow = now()->subMonths(2)->format('Y-m-d H:i:s');

    file_put_contents($path, implode("\n", [
        "[{$inWindow}] local.ERROR: Something broke {\"exception\":\"[object] (Exception)",
        '#0 continuation line that happens to mention .ERROR: but should not be double counted',
        "[{$outOfWindow}] local.ERROR: Old error outside the window",
        "[{$inWindow}] local.INFO: Not an error",
    ]));

    config(['logging.channels.single.path' => $path]);

    try {
        $this->artisan('admin:digest', ['type' => 'weekly'])
            ->assertExitCode(Command::SUCCESS);

        Mail::assertQueued(
            AdminDigestMail::class,
            fn (AdminDigestMail $mail): bool => $mail->data->errorLogCount === 1,
        );
    } finally {
        unlink($path);
    }
});
