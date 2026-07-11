<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'product_name'       => $this->product_name,
            'variant_label'      => $this->variant_label,
            'unit_price'         => (float) $this->unit_price,
            'quantity'           => (int) $this->quantity,
            'line_total'         => (float) $this->line_total,
        ];
    }
}
