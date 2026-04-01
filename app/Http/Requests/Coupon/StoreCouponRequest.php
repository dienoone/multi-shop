<?php

namespace App\Http\Requests\Coupon;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code'                     => ['required', 'string', 'max:50', 'alpha_dash'],
            'discount_type'            => ['required', new Enum(DiscountType::class)],
            'discount_value'           => ['required', 'numeric', 'min:0.01'],
            'minimum_order_amount'     => ['sometimes', 'numeric', 'min:0'],
            'maximum_discount_amount'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'usage_limit'              => ['sometimes', 'nullable', 'integer', 'min:1'],
            'expires_at'               => ['sometimes', 'nullable', 'date', 'after:now'],
            'is_active'                => ['sometimes', 'boolean'],
        ];
    }
}
