<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
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
    $response = $this->withSession(['success' => 'Saved', 'danger' => 'Nope'])->get('/watchlist');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('success', 'Saved')
        ->where('danger', 'Nope'));
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

test('the inertia middleware runs inside the web group after session and auth', function (): void {
    $web = app(Router::class)->getMiddlewareGroups()['web'];
    $global = app(Kernel::class)->getGlobalMiddleware();

    expect($web)->toContain(HandleInertiaRequests::class)
        ->and($global)->not->toContain(HandleInertiaRequests::class);
});
