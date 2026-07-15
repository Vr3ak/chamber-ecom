<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AdvanceOrderRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Notification;
use App\Models\Order;
use App\Services\OrderTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 *   POST /api/admin/orders/{order}/advance   (admin) move to next stage
 *   GET  /api/orders/{order}/tracking        (public) the timeline
 *   GET  /api/admin/notifications/failed     (admin) messages to retry
 */
class OrderTrackingController extends Controller
{
    public function __construct(private readonly OrderTrackingService $service)
    {
    }

    public function advance(AdvanceOrderRequest $request, Order $order): JsonResponse
    {
        $data  = $request->validated();
        $order = $this->service->advance(
            $order,
            $data['status'],
            $data['note'] ?? null,
            $data['tracking_number'] ?? null,
        );

        return response()->json([
            'order'    => new OrderResource($order),
            'tracking' => OrderTrackingResource::collection($this->timelineRows($order)),
        ]);
    }

    public function timeline(Order $order): AnonymousResourceCollection
    {
        return OrderTrackingResource::collection($this->timelineRows($order));
    }

    public function failedNotifications(): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            Notification::where('status', 'failed')->orderByDesc('id')->get()
        );
    }

    private const STAGE_NOTIFICATION = [
        'paid'      => 'order_confirmed',
        'shipped'   => 'order_shipped',
        'delivered' => 'order_delivered',
    ];

    private function timelineRows(Order $order)
    {
        $order->loadMissing(['tracking', 'notifications']);
        $byType = $order->notifications->keyBy('type');

        return $order->tracking->map(function ($row) use ($byType) {
            $type = self::STAGE_NOTIFICATION[$row->status] ?? null;
            $row->matched_notification = $type ? $byType->get($type) : null;

            return $row;
        });
    }
}