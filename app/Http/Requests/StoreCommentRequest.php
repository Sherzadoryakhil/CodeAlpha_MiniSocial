<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please write a comment before sending.',
            'body.max' => 'A comment can be at most 500 characters.',
        ];
    }
}
