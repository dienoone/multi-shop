<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'total_quantity' => $this->total_quantity,
            'total_price'    => round($this->total_price, 2),
            'items'          => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
