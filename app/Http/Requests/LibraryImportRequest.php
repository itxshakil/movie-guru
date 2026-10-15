<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LibraryImportRequest extends FormRequest
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
            'items' => ['required', 'array', 'max:500'],
            'items.*.imdb_id' => ['required', 'string', 'regex:/^tt\d{7,8}$/'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.poster' => ['nullable', 'string', 'max:500'],
            'items.*.year' => ['nullable', 'string', 'max:10'],
            'items.*.status' => ['required', Rule::enum(LibraryStatus::class)],
            'items.*.reaction' => ['nullable', Rule::enum(LibraryReaction::class), 'prohibited_unless:items.*.status,watched'],
            'items.*.watched_at' => ['nullable', 'date'],
        ];
    }
}
