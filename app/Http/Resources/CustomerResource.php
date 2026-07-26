<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A customer as the admin Customers screen needs them.
 *
 * @mixin User
 */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'orders_count' => (int) ($this->orders_count ?? 0),
            'is_suspended' => (bool) $this->is_suspended,
            'joined_at' => $this->created_at?->toDateString(),
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
