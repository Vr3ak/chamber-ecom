<?php

namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A single variant as the variant picker needs it.
 *
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'color'           => $this->whenLoaded('color', fn () => [
                'id'       => $this->color->id,
                'name'     => $this->color->name,
                'hex_code' => $this->color->hex_code,
            ]),
            'size'            => $this->whenLoaded('size', fn () => [
                'id'             => $this->size->id,
                'label'          => $this->size->label,
                'foot_length_cm' => $this->size->foot_length_cm !== null ? (float) $this->size->foot_length_cm : null,
            ]),
            'price'           => $this->price !== null ? (float) $this->price : null,
            'effective_price' => $this->effective_price,
            'stock_quantity'  => (int) $this->stock_quantity,
            'in_stock'        => $this->in_stock,
        ];
    }
}
