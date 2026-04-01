<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'code'                     => $this->code,
            'discount_type'            => $this->discount_type->value,
            'discount_value'           => $this->discount_value,
            'minimum_order_amount'     => $this->minimum_order_amount,
            'maximum_discount_amount'  => $this->maximum_discount_amount,
            'usage_limit'              => $this->usage_limit,
            'used_count'               => $this->used_count,
            'expires_at'               => $this->expires_at?->toDateTimeString(),
            'is_active'                => $this->is_active,
            'is_expired'               => $this->isExpired(),
            'is_exhausted'             => $this->isExhausted(),
            'created_at'               => $this->created_at->toDateTimeString(),
        ];
    }
}
