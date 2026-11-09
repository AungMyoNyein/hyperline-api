<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlaybackSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Publication is deliberately not filtered here: drafts must be refused
            // with the documented entitlement reason (403) rather than a 422, so the
            // denial path is identical for unpublished and unentitled viewers.
            'movie_id' => ['required', 'integer', Rule::exists('movies', 'id')],
            'source' => ['sometimes', 'nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'movie_id.exists' => 'The selected movie is not available.',
        ];
    }
}
