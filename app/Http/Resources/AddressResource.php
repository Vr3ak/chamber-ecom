<?php

namespace App\Http\Resources;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Address
 */
class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'street_line' => $this->street_line,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
