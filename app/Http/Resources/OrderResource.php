<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'order_number'     => $this->order_number,
            'status'           => $this->status->value,
            'payment_status'   => $this->payment_status,
            'subtotal'         => $this->subtotal,
            'discount_amount'  => $this->discount_amount,
            'shipping_amount'  => $this->shipping_amount,
            'tax_amount'       => $this->tax_amount,
            'total'            => $this->total,
            'currency'         => $this->currency,
            'shipping_address' => $this->shipping_address,
            'notes'            => $this->notes,
            'can_cancel'       => $this->status->canBeCancelled(),
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'customer'         => new UserResource($this->whenLoaded('user')),
            'created_at'       => $this->created_at->toDateTimeString(),
        ];
    }
}
