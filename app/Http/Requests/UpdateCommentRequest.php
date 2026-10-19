<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    // Checked before validation: only the author may update this comment.
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('comment')) ?? false;
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
            'body.required' => 'A comment cannot be empty.',
            'body.max' => 'A comment can be at most 500 characters.',
        ];
    }
}
