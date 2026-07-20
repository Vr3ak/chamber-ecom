<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 *   GET   /api/admin/orders                       all orders (+ stats), filter/search
 *   GET   /api/admin/orders/{order}                full detail incl. customer
 *   PATCH /api/admin/orders/{order}/tracking-number
 */
class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['user'])
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->boolean('refunded'), fn ($q) => $q->whereHas(
                'payments', fn ($p) => $p->where('status', 'refunded')
            ))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('order_number', 'like', '%'.$request->string('search').'%')
                ->orWhere('shipping_name', 'like', '%'.$request->string('search').'%')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders)
            ->additional(['stats' => $this->stats()])
            ->response();
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'items', 'payments', 'tracking',
            'user' => fn ($q) => $q->withCount('orders'),
        ]);

        return response()->json([
            'order' => new OrderResource($order),
            'tracking' => OrderTrackingResource::collection($order->trackingTimeline()),
        ]);
    }

    public function updateTrackingNumber(Request $request, Order $order): OrderResource
    {
        $data = $request->validate([
            'tracking_number' => ['required', 'string', 'max:60'],
        ]);

        $order->update($data);

        return OrderResource::make($order->fresh());
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        return [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'delivered')->count(),
            'refunded' => Order::whereHas('payments', fn ($p) => $p->where('status', 'refunded'))->count(),
        ];
    }
}
