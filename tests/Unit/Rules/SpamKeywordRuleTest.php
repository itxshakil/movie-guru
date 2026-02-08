<?php

declare(strict_types=1);

use App\Rules\SpamKeywordRule;
use Illuminate\Support\Facades\Log;

test('spam keyword rule blocks spam keywords', function (): void {
    Log::shouldReceive('channel')->with('spam-keyword')->andReturnSelf();
    Log::shouldReceive('info');
    $rule = new SpamKeywordRule();

    expect($rule->passes('message', 'How to make money fast'))->toBeFalse();
    expect($rule->passes('message', 'Check out my private photos'))->toBeFalse();
    expect($rule->passes('message', 'Earn $100 per day'))->toBeFalse();
    expect($rule->passes('message', 'Double your income today'))->toBeFalse();
});

test('spam keyword rule allows clean messages', function (): void {
    $rule = new SpamKeywordRule();

    expect($rule->passes('message', 'Hello, I have a question about a movie.'))->toBeTrue();
    expect($rule->passes('message', 'Could you please help me with my account?'))->toBeTrue();
    expect($rule->passes('message', 'Great website, keep up the good work!'))->toBeTrue();
});

test('spam keyword rule is case insensitive', function (): void {
    Log::shouldReceive('channel')->with('spam-keyword')->andReturnSelf();
    Log::shouldReceive('info');
    $rule = new SpamKeywordRule();

    expect($rule->passes('message', 'MAKE MONEY FAST'))->toBeFalse();
    expect($rule->passes('message', 'MaKe MoNeY fAsT'))->toBeFalse();
});

test('spam keyword rule logs detected spam keywords', function (): void {
    Log::shouldReceive('channel')
        ->with('spam-keyword')
        ->once()
        ->andReturnSelf();
    Log::shouldReceive('info')->once()->with(Mockery::pattern('/Spam Keyword Detected: make money/'));

    $rule = new SpamKeywordRule();
    $rule->passes('message', 'How to make money fast');

    expect(true)->toBeTrue();
});
