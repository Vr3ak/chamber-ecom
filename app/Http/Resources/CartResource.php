<?php

namespace App\Http\Resources;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items');

        return [
            'id' => $this->id,
            'status' => $this->status,
            'items' => CartItemResource::collection($items),
            'items_count' => $this->when($items !== null, fn () => $items->sum('quantity')),
            'subtotal' => $this->when($items !== null, fn () => (float) $items->sum(
                fn ($item) => ($item->variant?->effective_price ?? 0) * $item->quantity
            )),
        ];
    }
}
