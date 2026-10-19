<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    // Checked before validation: only the author may update this post.
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('post')) ?? false;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'A post cannot be empty.',
            'body.max' => 'A post can be at most 1000 characters.',
            'image.image' => 'The attachment must be an image file.',
            'image.mimes' => 'The image must be a JPG, PNG or WebP file.',
            'image.max' => 'The image must not be larger than 2 MB.',
        ];
    }
}
