<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->whenLoaded('variant');
        $unitPrice = $this->variant?->effective_price ?? 0;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price' => (float) $unitPrice,
            'line_total' => (float) $unitPrice * $this->quantity,
            'insufficient_stock' => $this->when(
                $this->relationLoaded('variant') && $this->variant,
                fn () => $this->variant->stock_quantity < $this->quantity
            ),
            'variant' => ProductVariantResource::make($variant),
            'product' => $this->whenLoaded('variant', fn () => $this->variant?->product ? [
                'id' => $this->variant->product->id,
                'name' => $this->variant->product->name,
                'slug' => $this->variant->product->slug,
                'thumbnail' => optional($this->variant->product->images->first())->url,
            ] : null),
        ];
    }
}
