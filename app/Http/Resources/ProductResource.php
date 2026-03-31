<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'price'          => $this->price,
            'compare_price'  => $this->compare_price,
            'discount_percent' => $this->compare_price
                ? round((($this->compare_price - $this->price) / $this->compare_price) * 100)
                : null,
            'stock_quantity' => $this->stock_quantity,
            'in_stock'       => $this->stock_quantity > 0,
            'sku'            => $this->sku,
            'is_active'      => $this->is_active,
            'images'         => $this->images ?? [],
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}
