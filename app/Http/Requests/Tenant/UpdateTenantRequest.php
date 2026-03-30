<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'subdomain' => ['sometimes', 'string', 'max:100', 'alpha_dash'],
            'email'     => ['sometimes', 'email'],
            'plan'      => ['sometimes', 'in:free,starter,pro'],
            'is_active' => ['sometimes', 'boolean'],
            'settings'  => ['sometimes', 'array'],
        ];
    }
}
