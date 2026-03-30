<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'subdomain'  => $this->subdomain,
            'domain'     => $this->domain,
            'email'      => $this->email,
            'plan'       => $this->plan,
            'is_active'  => $this->is_active,
            'settings'   => $this->settings,
            'store_url'  => 'http://' . $this->subdomain . '.' . config('app.base_domain'),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
