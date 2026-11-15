<?php

declare(strict_types=1);

use App\Notifications\DatabaseApproachingMaxConnections;
use App\Notifications\SlowQueryDetected;
use App\Services\Core\AdminAlerter;
use Illuminate\Database\Events\DatabaseBusy;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * A request whose queries add up to more than the slow-query threshold.
 */
function registerSlowRoute(): void
{
    Route::get('/_test/slow', static function (): string {
        DB::connection()->logQuery('select 1', [], 150.0);

        return 'done';
    });
}

test('a slow request still succeeds when the mail server refuses the alert', function (): void {
    Mail::extend('refusing', static fn (): AbstractTransport => new class extends AbstractTransport
    {
        public function __toString(): string
        {
            return 'refusing';
        }

        protected function doSend(SentMessage $message): void
        {
            throw new TransportException('451 4.7.1 Ratelimit "hostinger_out_ratelimit" exceeded');
        }
    });
    config()->set('mail.mailers.refusing', ['transport' => 'refusing']);
    config()->set('mail.default', 'refusing');
    config()->set('mail.admin.address', 'admin@movieguru.test');

    $warnings = collect();
    Log::listen(static function (MessageLogged $log) use ($warnings): void {
        if ($log->level === 'warning') {
            $warnings->push($log);
        }
    });
    registerSlowRoute();

    $this->get('/_test/slow')->assertSuccessful()->assertSee('done');

    expect($warnings)->toHaveCount(1)
        ->and($warnings->first()->message)->toBe('Could not send the admin alert email.')
        ->and($warnings->first()->context['kind'])->toBe('slow-query')
        ->and($warnings->first()->context['error'])->toContain('hostinger_out_ratelimit');
});

test('a slow request emails the admin about it', function (): void {
    Notification::fake();
    config()->set('mail.admin.address', 'admin@movieguru.test');
    registerSlowRoute();

    $this->get('/_test/slow')->assertSuccessful();

    Notification::assertSentOnDemand(
        SlowQueryDetected::class,
        static fn (SlowQueryDetected $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'admin@movieguru.test',
    );
});

test('each kind of alert is emailed at most once per window', function (): void {
    Notification::fake();
    $this->withoutDefer();
    config()->set('mail.admin.address', 'admin@movieguru.test');
    $alerter = resolve(AdminAlerter::class);
    $alert = static fn (): DatabaseApproachingMaxConnections => new DatabaseApproachingMaxConnections('mysql', 90);

    $alerter->send('database-busy', $alert());
    $alerter->send('database-busy', $alert());
    $alerter->send('slow-query', $alert());

    Notification::assertSentOnDemandTimes(DatabaseApproachingMaxConnections::class, 2);

    $this->travel(AdminAlerter::WINDOW_SECONDS + 1)->seconds();
    $alerter->send('database-busy', $alert());

    Notification::assertSentOnDemandTimes(DatabaseApproachingMaxConnections::class, 3);
});

test('a busy database alerts the configured admin instead of a placeholder address', function (): void {
    Notification::fake();
    $this->withoutDefer();
    config()->set('mail.admin.address', 'admin@movieguru.test');

    event(new DatabaseBusy('mysql', 90));

    Notification::assertSentOnDemand(
        DatabaseApproachingMaxConnections::class,
        static fn (DatabaseApproachingMaxConnections $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'admin@movieguru.test' && $notification->connections === 90,
    );
});

test('no alert is sent while no admin address is configured', function (): void {
    Notification::fake();
    $this->withoutDefer();
    config()->set('mail.admin.address', '');

    resolve(AdminAlerter::class)->send('slow-query', new DatabaseApproachingMaxConnections('mysql', 90));

    Notification::assertNothingSent();
});
