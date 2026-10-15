<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\RequestLogging\RequestLogFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

test('a 304 not modified response is logged without crashing', function (): void {
    Route::get('/__request-logger/not-modified', fn () => response('', 304));

    $this->get('/__request-logger/not-modified')->assertStatus(304);
});

test('a redirect from a route without a session is logged without crashing', function (): void {
    Route::get('/__request-logger/redirect', fn () => redirect('/'));

    $this->get('/__request-logger/redirect')->assertRedirect('/');
});

test('validation redirects are still logged', function (): void {
    $this->actingAs(User::factory()->create())
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), ['name' => '', 'email' => 'not-an-email'])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors(['name', 'email']);
});

test('requests get a request id header', function (): void {
    $response = $this->get(route('protocol-link.show', ['url' => 'web+movieguru://watchlist']));

    expect($response->headers->get('X-Request-ID'))->toMatch('/^\d{6}$/');
});

test('passwords are masked in request log lines', function (): void {
    $request = Request::create('/login', 'POST', ['email' => 'a@example.com', 'password' => 'hunter22', 'nested' => ['password' => 'x']]);

    $line = resolve(RequestLogFormatter::class)->formatRequest($request, 123456);

    expect($line)
        ->toContain('#123456 POST /login')
        ->toContain('"password":"****"')
        ->not->toContain('hunter22');
});
