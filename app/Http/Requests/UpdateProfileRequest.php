<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\PictureRules;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'current_password' => ['required_with:password', 'current_password:web'],
            'password' => ['nullable', 'string', 'min:10', 'confirmed'],
            'photo' => PictureRules::upload(),
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}