<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please write something before posting.',
            'body.max' => 'A post can be at most 1000 characters.',
            'image.image' => 'The attachment must be an image file.',
            'image.mimes' => 'The image must be a JPG, PNG or WebP file.',
            'image.max' => 'The image must not be larger than 2 MB.',
        ];
    }
}
