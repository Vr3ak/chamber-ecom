<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'rating'      => (int) $this->rating,
            'comment'     => $this->comment,
            'is_verified' => (bool) $this->is_verified,
            'author'      => $this->whenLoaded('user', fn () => $this->user->name),
            'created_at'  => $this->created_at?->toDateString(),
        ];
    }
}