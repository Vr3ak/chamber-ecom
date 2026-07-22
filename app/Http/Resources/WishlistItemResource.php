<?php

namespace App\Http\Resources;

use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WishlistItem
 */
class WishlistItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'base_price' => (float) $this->product->base_price,
                'is_active' => (bool) $this->product->is_active,
                'thumbnail' => $this->product->relationLoaded('images')
                    ? optional($this->product->images->first())->url
                    : null,
            ]),
        ];
    }
}
