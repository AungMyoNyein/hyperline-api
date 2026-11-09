<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWatchProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'movie_id' => ['required', 'integer', Rule::exists('movies', 'id')->where('published', true)],
            'position_seconds' => ['required', 'integer', 'between:0,864000'],
            'duration_seconds' => ['sometimes', 'nullable', 'integer', 'between:1,864000'],
            'completed' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $position = $this->integer('position_seconds');
            $duration = $this->filled('duration_seconds') ? $this->integer('duration_seconds') : null;

            if ($duration !== null && $position > $duration) {
                $validator->errors()->add('position_seconds', 'Playback position cannot exceed the duration.');
            }
        });
    }
}
