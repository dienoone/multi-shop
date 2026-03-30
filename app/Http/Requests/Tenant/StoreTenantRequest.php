<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Store details
            'name'       => ['required', 'string', 'max:255'],
            'subdomain'  => ['required', 'string', 'max:100', 'alpha_dash', 'unique:tenants,subdomain'],
            'email'      => ['required', 'email', 'unique:tenants,email'],
            'plan'       => ['sometimes', 'in:free,starter,pro'],
            'is_active'  => ['sometimes', 'boolean'],
            'settings'   => ['sometimes', 'array'],

            // Store owner account
            'owner_name'     => ['required', 'string', 'max:255'],
            'owner_email'    => ['required', 'email'],
            'owner_password' => ['required', 'string', 'min:8'],
        ];
    }
}
