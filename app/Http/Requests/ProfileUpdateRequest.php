<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:50'],
            'username' => [
                'required', 'string', 'min:3', 'max:30', 'regex:/^[A-Za-z0-9_]+$/',
                Rule::unique(User::class)->ignore($userId),
            ],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            'bio' => ['nullable', 'string', 'max:160'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain letters, numbers and underscores.',
            'username.unique' => 'That username is already taken. Please choose another one.',
            'email.unique' => 'An account with this email already exists.',
            'photo.image' => 'The photo must be an image file.',
            'photo.mimes' => 'The photo must be a JPG, PNG or WebP image.',
            'photo.max' => 'The photo must not be larger than 2 MB.',
        ];
    }
}
