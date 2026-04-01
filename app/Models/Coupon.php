<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'code', 'discount_type', 'discount_value', 'minimum_order_amount', 'maximum_discount_amount', 'usage_limit', 'used_count', 'expires_at', 'is_active',])]
class Coupon extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'discount_type'            => DiscountType::class,
            'discount_value'           => 'decimal:2',
            'minimum_order_amount'     => 'decimal:2',
            'maximum_discount_amount'  => 'decimal:2',
            'is_active'                => 'boolean',
            'expires_at'               => 'datetime',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null
            && $this->used_count >= $this->usage_limit;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = match ($this->discount_type) {
            DiscountType::Fixed      => (float) $this->discount_value,
            DiscountType::Percentage => $subtotal * ($this->discount_value / 100),
        };

        // Cap percentage discounts at maximum_discount_amount if set
        if (
            $this->discount_type === DiscountType::Percentage
            && $this->maximum_discount_amount !== null
        ) {
            $discount = min($discount, (float) $this->maximum_discount_amount);
        }

        // Discount can never exceed the subtotal
        return min($discount, $subtotal);
    }
}
