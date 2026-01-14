<?php

declare(strict_types=1);

use App\Rules\BlockTemporaryEmailRule;

test('block temporary email rule blocks temporary emails', function (): void {
    $rule = new BlockTemporaryEmailRule();

    expect($rule->passes('email', 'user@mailinator.com'))->toBeFalse();
    expect($rule->passes('email', 'test@guerrillamail.com'))->toBeFalse();
    expect($rule->passes('email', 'spam@10mail.org'))->toBeFalse();
    expect($rule->passes('email', 'example@gmail.com'))->toBeFalse(); // 'example' is a blocked pattern
});

test('block temporary email rule allows valid emails', function (): void {
    $rule = new BlockTemporaryEmailRule();

    expect($rule->passes('email', 'john.doe@gmail.com'))->toBeTrue();
    expect($rule->passes('email', 'support@movie-guru.com'))->toBeTrue();
});
