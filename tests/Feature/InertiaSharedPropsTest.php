<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('the authenticated user is shared with inertia pages', function (): void {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();

    $response = $this->get('/profile');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Profile/Edit')
        ->where('auth.user.id', $user->id)
        ->where('auth.user.email', $user->email));
});

test('guests have no shared user', function (): void {
    $response = $this->get('/watchlist');

    $response->assertInertia(fn (Assert $page) => $page->where('auth.user', null));
});

test('flash messages are shared with inertia pages', function (): void {
    $response = $this->withSession(['success' => 'Saved', 'error' => 'Nope'])->get('/watchlist');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('success', 'Saved')
        ->where('error', 'Nope'));
});

test('a controller flash message reaches the next inertia page', function (): void {
    $this->from('/watchlist')->post('/contact', [
        'name' => 'Jane',
        'email' => 'jane@gmail.com',
        'message' => 'Hello there',
    ])->assertSessionHasNoErrors()->assertSessionHas('success')->assertRedirect('/watchlist');

    $this->get('/watchlist')->assertInertia(fn (Assert $page) => $page
        ->where('success', "Thanks for your message. We'll be in touch."));
});

/**
 * Regression guard: Inertia shares session-backed props, so its middleware must run after StartSession.
 */
test('the inertia middleware is sorted after the session middleware on web routes', function (): void {
    $router = app(Router::class);
    $route = $router->getRoutes()->match(Request::create('/profile'));
    $stack = array_values($router->gatherRouteMiddleware($route));

    $sessionIndex = array_search(StartSession::class, $stack, true);
    $inertiaIndex = array_search(HandleInertiaRequests::class, $stack, true);

    expect($sessionIndex)->toBeInt()
        ->and($inertiaIndex)->toBeInt()
        ->and($sessionIndex)->toBeLessThan($inertiaIndex);
});

test('a flashed error is shared with inertia pages', function (): void {
    $response = $this->withSession(['error' => 'Something broke'])->get('/watchlist');

    $response->assertInertia(fn (Assert $page) => $page->where('error', 'Something broke'));
});

test('an expired page redirects back with an error flash', function (): void {
    Route::post('/_expired', fn () => throw new TokenMismatchException())->middleware('web');

    $response = $this->from('/contact')->post('/_expired');

    $response->assertRedirect('/contact')->assertSessionHas('error', 'The page expired, please try again.');
});
