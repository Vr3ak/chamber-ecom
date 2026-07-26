<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order_number' => $this->whenLoaded('order', fn () => $this->order?->order_number),
            'channel' => $this->channel,
            'type' => $this->type,
            'recipient' => $this->recipient,
            'status' => $this->status,
            'sent_at' => $this->sent_at?->toDateTimeString(),
        ];
    }
}
