<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'email_verified' => !is_null($this->email_verified_at),
            'linked_providers' => $this->whenLoaded('socialAccounts', fn() => $this->socialAccounts->pluck('provider')),
            'created_at' => $this->created_at->toDateTimeString()
        ];
    }
}
