<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The full payload the Detailed Product Page (Feature 1) consumes:
 * description, gallery, the variant picker options (colours + sizes with
 * the foot-size guide), category breadcrumb, the Trending badge, and the
 * rating summary (AVG + COUNT).
 *
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
            'base_price'  => (float) $this->base_price,
            'is_active'   => (bool) $this->is_active,
            'is_trending' => $this->is_trending,

            'brand'       => $this->whenLoaded('brand', fn () => [
                'id'   => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),

            // Category breadcrumb(s): each attached category's root->leaf trail.
            'breadcrumbs' => $this->whenLoaded('categories', fn () =>
                $this->categories->map(fn ($c) => $c->breadcrumb())->values()),

            'images'      => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'url'        => $img->url,
                'alt'        => $img->alt,
                'color_id'   => $img->color_id,
                'is_primary' => (bool) $img->is_primary,
            ])->values()),

            'variants'    => ProductVariantResource::collection($this->whenLoaded('variants')),

            // Distinct colour & size options for the picker, derived from variants.
            'options'     => $this->when($this->relationLoaded('variants'), fn () => [
                'colors' => $this->variants
                    ->loadMissing('color')->pluck('color')->filter()->unique('id')
                    ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'hex_code' => $c->hex_code])
                    ->values(),
                'sizes'  => $this->variants
                    ->loadMissing('size')->pluck('size')->filter()->unique('id')
                    ->sortBy('sort_order')
                    ->map(fn ($s) => [
                        'id'             => $s->id,
                        'label'          => $s->label,
                        'foot_length_cm' => $s->foot_length_cm !== null ? (float) $s->foot_length_cm : null,
                    ])->values(),
            ]),

            // Rating summary — AVG(rating) and COUNT(*) (Advanced SQL doc 1.1).
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
