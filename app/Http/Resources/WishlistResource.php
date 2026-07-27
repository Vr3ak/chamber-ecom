<?php

namespace App\Http\Resources;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Wishlist
 */
class WishlistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // ->resolve() so this is a plain list — see CartResource.
            'items' => $this->whenLoaded(
                'items',
                fn () => WishlistItemResource::collection($this->items)->resolve($request),
            ),
        ];
    }
}
