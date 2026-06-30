<?php

namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'sku'             => $this->sku,
            'color'           => $this->whenLoaded('color', fn () => [
                'id'   => $this->color->id,
                'name' => $this->color->name,
                'hex'  => $this->color->hex,
            ]),
            'size'            => $this->whenLoaded('size', fn () => [
                'id'    => $this->size->id,
                'label' => $this->size->label,
                'cm'    => $this->size->cm !== null ? (float) $this->size->cm : null,
            ]),
            'price'           => $this->price !== null ? (float) $this->price : null,
            'effective_price' => $this->effective_price,
            'stock_quantity'  => (int) $this->stock_quantity,
            'in_stock'        => $this->in_stock,
        ];
    }
}