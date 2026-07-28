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

class OrderTrackingController extends Controller
{
    public function __construct(private readonly OrderTrackingService $service) {}

    public function advance(AdvanceOrderRequest $request, Order $order): JsonResponse
    {
        $data = $request->validated();
        $order = $this->service->advance(
            $order,
            $data['status'],
            $data['note'] ?? null,
            $data['tracking_number'] ?? null,
        );

        return response()->json([
            'order' => new OrderResource($order),
            'tracking' => OrderTrackingResource::collection($order->trackingTimeline()),
        ]);
    }

    public function timeline(Order $order): AnonymousResourceCollection
    {
        return OrderTrackingResource::collection($order->trackingTimeline());
    }

    public function failedNotifications(): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            Notification::where('status', 'failed')->orderByDesc('id')->get()
        );
    }
}
