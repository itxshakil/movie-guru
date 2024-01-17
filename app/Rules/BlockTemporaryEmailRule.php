<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BlockTemporaryEmailRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $blockedPatterns = [
            '/\b(?:example|test|spam)\b/i',
            '/\b(?:mailinator\.com|guerrillamail\.com|10mail\.org)\b/i',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return 'Invalid :attribute detected. Please provide a valid email address.';
    }
}
