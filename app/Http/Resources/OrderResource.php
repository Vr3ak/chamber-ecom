<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'order_number'  => $this->order_number,
            'status'        => $this->status,
            'subtotal'      => (float) $this->subtotal,
            'total'         => (float) $this->total,
            'is_paid'       => $this->isPaid(),
            'shipping'      => [
                'name'    => $this->shipping_name,
                'phone'   => $this->shipping_phone,
                'address' => $this->shipping_address,
            ],
            'tracking_number' => $this->tracking_number,
            'placed_at'     => $this->placed_at?->toDateTimeString(),
            'items'         => OrderItemResource::collection($this->whenLoaded('items')),
            'payments'      => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
