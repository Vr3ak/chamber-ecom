<?php

namespace App\Http\Resources;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Size
 */
class SizeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'foot_length_cm' => $this->foot_length_cm !== null ? (float) $this->foot_length_cm : null,
            'sort_order' => (int) $this->sort_order,
        ];
    }
}
