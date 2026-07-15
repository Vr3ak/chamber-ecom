<?php

namespace App\Http\Resources;

use App\Models\OrderTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderTracking
 */
class OrderTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'stage'        => $this->status,
            'note'         => $this->note,
            'time'         => $this->created_at?->toDateTimeString(),
            'notification' => $this->whenNotNull(
                $this->matched_notification
                    ? [
                        'channel' => $this->matched_notification->channel,
                        'type'    => $this->matched_notification->type,
                        'status'  => $this->matched_notification->status,
                        'sent_at' => $this->matched_notification->sent_at?->toDateTimeString(),
                    ]
                    : null
            ),
        ];
    }
}