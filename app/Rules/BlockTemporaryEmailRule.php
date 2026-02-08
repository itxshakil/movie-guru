<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

final class BlockTemporaryEmailRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $blockedPatterns = [
            '/\b(?:example|test|spam)\b/i',
            '/\b(?:mailinator\.com|guerrillamail\.com|10mail\.org)\b/i',
        ];

        return array_all(
            $blockedPatterns,
            static fn($blockedPattern): bool => !preg_match($blockedPattern, (string)$value),
        );
    }

    public function message(): string
    {
        return 'Invalid :attribute detected. Please provide a valid email address.';
    }
}
