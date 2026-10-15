<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class WatchlistImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'max:500'],
            'items.*.imdb_id' => ['required', 'string', 'max:20'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.poster' => ['nullable', 'string', 'max:500'],
            'items.*.year' => ['nullable', 'string', 'max:10'],
        ];
    }
}
