<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'size_guide'  => $this->size_guide,
            'base_price'  => (float) $this->base_price,
            'is_active'   => (bool) $this->is_active,

            'brand'       => $this->whenLoaded('brand', fn () => [
                'id'   => $this->brand->id,
                'name' => $this->brand->name,
            ]),

            'images'      => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'url'      => $img->url,
                'alt'      => $img->alt,
                'color_id' => $img->color_id,
            ])->values()),

            'variants'    => ProductVariantResource::collection($this->whenLoaded('variants')),

            'options'     => $this->when($this->relationLoaded('variants'), fn () => [
                'colors' => $this->variants
                    ->loadMissing('color')->pluck('color')->filter()->unique('id')
                    ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'hex' => $c->hex])
                    ->values(),
                'sizes'  => $this->variants
                    ->loadMissing('size')->pluck('size')->filter()->unique('id')
                    ->sortBy('label')
                    ->map(fn ($s) => ['id' => $s->id, 'label' => $s->label, 'cm' => $s->cm !== null ? (float) $s->cm : null])
                    ->values(),
            ]),

            'rating'      => [
                'average' => $this->reviews_avg_rating !== null ? round((float) $this->reviews_avg_rating, 1) : null,
                'count'   => (int) ($this->reviews_count ?? 0),
            ],

            'reviews'     => ReviewResource::collection($this->whenLoaded('reviews')),

            'total_stock' => $this->when(
                $this->variants_sum_stock_quantity !== null || $this->relationLoaded('variants'),
                fn () => $this->total_stock
            ),
        ];
    }
}