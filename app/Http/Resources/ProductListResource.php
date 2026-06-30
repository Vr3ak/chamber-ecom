<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'brand'       => $this->whenLoaded('brand', fn () => $this->brand->name),
            'base_price'  => (float) $this->base_price,
            'from_price'  => $this->whenLoaded('variants', function () {
                $prices = $this->variants
                    ->map(fn ($v) => (float) ($v->price ?? $this->base_price));

                return $prices->isNotEmpty() ? $prices->min() : (float) $this->base_price;
            }),
            'thumbnail'   => $this->whenLoaded('images', fn () => optional($this->images->first())->url),
            'avg_rating'  => $this->reviews_avg_rating !== null ? round((float) $this->reviews_avg_rating, 1) : null,
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'is_active'   => (bool) $this->is_active,
        ];
    }
}