<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'shipping_address'              => ['required', 'array'],
            'shipping_address.full_name'    => ['required', 'string', 'max:255'],
            'shipping_address.phone'        => ['required', 'string', 'max:30'],
            'shipping_address.address_line' => ['required', 'string', 'max:255'],
            'shipping_address.city'         => ['required', 'string', 'max:100'],
            'shipping_address.state'        => ['sometimes', 'string', 'max:100'],
            'shipping_address.postal_code'  => ['required', 'string', 'max:20'],
            'shipping_address.country'      => ['required', 'string', 'size:2'],
            'notes'                         => ['sometimes', 'nullable', 'string', 'max:500'],
            'currency'                      => ['sometimes', 'string', 'size:3'],
            'shipping_amount'               => ['sometimes', 'numeric', 'min:0'],
            'tax_amount'                    => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
