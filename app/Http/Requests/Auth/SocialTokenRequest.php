<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'access_token.required' => 'The provider access token is required.',
        ];
    }
}
