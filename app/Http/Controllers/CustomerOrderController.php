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

/**
 * The signed-in customer's own orders (Figma 04 — Account & Order History).
 *
 * Every query is scoped to auth()->id(); the order is never trusted from the
 * route alone. Distinct from Api\Admin\OrderController, which sees all orders.
 */
class CustomerOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user()->orders()
            ->with(['items', 'payments'])
            ->latest('placed_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('orders/index', [
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

        // Which line items this customer may still review. An order can be
        // reviewed at any stage; the only limit is one review per product
        // per customer.
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

    /** Write-a-Review modal (Figma node 48:3645). */
    public function review(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== $request->user()->id, 404);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        // The product must actually be on this order — otherwise a customer
        // could review anything in the catalogue off the back of one purchase.
        abort_unless(
            $order->items()->whereHas('variant', fn ($q) => $q->where('product_id', $data['product_id']))->exists(),
            403,
            'That product is not on this order.'
        );

        // Written against one of the customer's own orders, so it still counts
        // as a verified purchase whatever stage that order is at.
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
