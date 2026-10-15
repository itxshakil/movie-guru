<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LibraryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'poster' => ['nullable', 'string', 'max:500'],
            'year' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::enum(LibraryStatus::class)],
            'reaction' => ['nullable', Rule::enum(LibraryReaction::class), 'prohibited_unless:status,watched'],
        ];
    }
}
