<?php

declare(strict_types=1);

test('contact form submission', function (): void {
    $this->post(route('contact.store'), [
        'name' => 'John Doe',
        'email' => 'john@gmail.com',
        'message' => 'Hello world!',
    ])->assertRedirect('/');

    $this->assertDatabaseHas('contacts', [
        'name' => 'John Doe',
        'email' => 'john@gmail.com',
        'message' => 'Hello world!',
    ]);

    $this->assertDatabaseCount('contacts', 1);
});

test('contact form submission with json', function (): void {
    $this->postJson(route('contact.store'), [
        'name' => 'John Doe',
        'email' => 'john@gmail.com',
        'message' => 'Hello world!',
    ])->assertJson([
        'message' => "Thanks for your message. We'll be in touch.",
    ]);

    $this->assertDatabaseHas('contacts', [
        'name' => 'John Doe',
        'email' => 'john@gmail.com',
        'message' => 'Hello world!',
    ]);

    $this->assertDatabaseCount('contacts', 1);
});

test('contact form submission with invalid data', function (): void {
    $this->post(route('contact.store'), [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'message' => 'Hello world!',
    ])->assertSessionHasErrors([
        'email' => 'The email field must be a valid email address.',
    ]);

    $this->assertDatabaseCount('contacts', 0);
});

test('contact form submission with invalid data with json', function (): void {
    $this->postJson(route('contact.store'), [
        'name' => '',
        'email' => 'invalid-email',
        'message' => 'Hello world!',
    ])->assertJsonValidationErrors([
        'name' => 'The name field is required.',
        'email' => 'The email field must be a valid email address.',
    ]);

    $this->assertDatabaseCount('contacts', 0);
});
