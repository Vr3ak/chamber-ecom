<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order;
use App\Services\OrderTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Admin order management (Figma 07 — Admin · Orders). */
class OrderAdminController extends Controller
{
    /** Mirrors the orders.status enum; 'pending' is the initial state only. */
    private const STAGES = ['paid', 'packed', 'shipped', 'delivered', 'cancelled'];

    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $term = trim((string) $request->query('q', ''));

        $orders = Order::query()
            ->with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
                $q->where('order_number', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/orders/index', [
            'query' => $term,
            'status' => $status,
            'orders' => collect($orders->items())->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->user?->name,
                'customer_email' => $o->user?->email,
                'total' => (float) $o->total,
                'status' => $o->status,
                'is_paid' => $o->isPaid(),
                'placed_at' => $o->placed_at?->toDateString(),
            ])->all(),
            'pagination' => [
                'current' => $orders->currentPage(),
                'last' => $orders->lastPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items', 'payments', 'tracking', 'user']);

        return Inertia::render('admin/orders/show', [
            'order' => OrderResource::make($order)->resolve(),
            'timeline' => OrderTrackingResource::collection($order->trackingTimeline())->resolve(),
            'stages' => self::STAGES,
        ]);
    }

    /** Move the order to its next fulfilment stage. */
    public function advance(
        Request $request,
        Order $order,
        OrderTrackingService $tracking,
    ): RedirectResponse {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(self::STAGES)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $tracking->advance($order, $data['status'], $data['note'] ?? null);

        return back()->with('success', 'Order updated.');
    }

    public function updateTrackingNumber(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'tracking_number' => ['nullable', 'string', 'max:64'],
        ]);

        $order->update($data);

        return back()->with('success', 'Tracking number saved.');
    }
}
