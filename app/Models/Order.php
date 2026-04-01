<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'user_id', 'order_number', 'status', 'subtotal', 'discount_amount', 'shipping_amount', 'tax_amount', 'total', 'currency', 'shipping_address', 'notes', 'payment_intent_id', 'payment_status',])]
class Order extends Model
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'status'           => OrderStatus::class,
            'shipping_address' => 'array',
            'subtotal'         => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'shipping_amount'  => 'decimal:2',
            'tax_amount'       => 'decimal:2',
            'total'            => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
