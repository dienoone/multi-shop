<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['tenant_id', 'category_id', 'name', 'slug', 'description', 'price', 'compare_price', 'stock_quantity', 'sku', 'is_active', 'images',])]
class Product extends Model
{
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'compare_price'  => 'decimal:2',
            'is_active'      => 'boolean',
            'images'         => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug ??= Str::slug($product->name);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
