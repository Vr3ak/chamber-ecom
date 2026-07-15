<?php

namespace App\Services;

use App\Models\Order;

/**
 * Advances an order to a new fulfilment stage: logs a tracking row, updates
 * orders.status, and sends the email notification that stage triggers.
 */
class OrderTrackingService
{
    private const NOTIFY_MAP = [
        'paid'      => ['order_confirmed'],
        'packed'    => [],
        'shipped'   => ['order_shipped'],
        'delivered' => ['order_delivered'],
        'cancelled' => ['order_update'],
    ];

    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function advance(Order $order, string $status, ?string $note = null, ?string $tracking = null): Order
    {
        if ($tracking) {
            $order->tracking_number = $tracking;
        }

        $order->tracking()->create([
            'status'     => $status,
            'note'       => $note,
            'created_at' => now(),
        ]);

        $order->status = $status;
        $order->save();

        foreach (self::NOTIFY_MAP[$status] ?? [] as $type) {
            $this->notifications->notify($order, $type);
        }

        return $order->fresh()->load(['items', 'tracking', 'notifications']);
    }
}