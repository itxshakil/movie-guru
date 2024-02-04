<?php

test('contact form submission', function () {
    $this->post(route('contact'), [
        'name' => 'John Doe',
        'email' => 'test@test.com',
        'message' => 'Hello world!',
    ])->assertRedirect(route('contact'));

    $this->assertDatabaseHas('contacts', [
        'name' => 'John Doe',
        'email' => 'test@test.com',
        'message' => 'Hello world!',
    ]);

    $this->assertDatabaseCount('contacts', 1);
});

test('contact form submission with json', function () {
    $this->postJson(route('contact'), [
        'name' => 'John Doe',
        'email' => 'test@test.com',
        'message' => 'Hello world!',
    ])->assertJson([
        'message' => 'Thanks for your message. We\'ll be in touch.',
    ]);

    $this->assertDatabaseHas('contacts', [
        'name' => 'John Doe',
        'email' => 'test@test.com',
        'message' => 'Hello world!',
    ]);

    $this->assertDatabaseCount('contacts', 1);

    $this->assertDatabaseHas('notifications', [
        'type' => \App\Notifications\ContactFormSubmission::class,
        'notifiable_type' => \App\Models\User::class,
        'notifiable_id' => 1,
    ]);

    $this->assertDatabaseCount('notifications', 1);
});

test('contact form submission with invalid data', function () {
    $this->post(route('contact'), [
        'name' => 'John Doe',
        'email' => 'test',
        'message' => 'Hello world!',
    ])->assertSessionHasErrors([
        'email' => 'The email must be a valid email address.',
    ]);

    $this->assertDatabaseCount('contacts', 0);
});

test('contact form submission with invalid data with json', function () {
    $this->postJson(route('contact'), [
        'name' => '',
        'email' => 'test',
        'message' => 'Hello world!',
    ])->assertJsonValidationErrors([
        'name' => 'The name field is required.',
        'email' => 'The email must be a valid email address.',
    ]);

    $this->assertDatabaseCount('contacts', 0);
});
