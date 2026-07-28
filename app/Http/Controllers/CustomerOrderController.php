<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerOrderController extends Controller
{
    private const STATUS_TABS = [
        'processing' => ['pending', 'paid', 'packed'],
        'shipped' => ['shipped'],
        'delivered' => ['delivered'],
        'cancelled' => ['cancelled'],
    ];

    public function index(Request $request): Response
    {
        $tab = (string) $request->query('status', '');
        $statuses = self::STATUS_TABS[$tab] ?? null;

        $orders = $request->user()->orders()
            ->with(['items', 'payments'])
            ->when($statuses, fn ($q) => $q->whereIn('status', $statuses))
            ->latest('placed_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('orders/index', [
            'status' => $statuses ? $tab : null,
            'orders' => OrderResource::collection($orders->items())->resolve(),
            'pagination' => [
                'current' => $orders->currentPage(),
                'last' => $orders->lastPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_if($order->user_id !== $request->user()->id, 404);

        $order->load(['items.variant.product', 'payments', 'tracking']);

        $reviewed = Review::query()
            ->where('user_id', $request->user()->id)
            ->pluck('product_id')
            ->all();

        return Inertia::render('orders/show', [
            'order' => OrderResource::make($order)->resolve(),
            'timeline' => OrderTrackingResource::collection($order->trackingTimeline())->resolve(),
            'reviewable' => $order->items
                ->map(fn ($item) => [
                    'order_item_id' => $item->id,
                    'product_id' => $item->variant?->product_id,
                    'product_name' => $item->product_name,
                ])
                ->filter(fn ($row) => $row['product_id'] && ! in_array($row['product_id'], $reviewed, true))
                ->values()
                ->all(),
        ]);
    }

    public function review(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== $request->user()->id, 404);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless(
            $order->items()->whereHas('variant', fn ($q) => $q->where('product_id', $data['product_id']))->exists(),
            403,
            'That product is not on this order.'
        );

        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $data['product_id']],
            [
                'order_id' => $order->id,
                'rating' => $data['rating'],
                'body' => $data['body'] ?? null,
                'is_verified' => true,
            ],
        );

        return back();
    }
}
