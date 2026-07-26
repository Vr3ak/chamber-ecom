<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/admin/dashboard — the Overview screen's stat tiles, order-status
 * donut, recent orders, and top products.
 */
class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'total_revenue' => (float) Payment::where('status', 'succeeded')->sum('amount'),
            'total_orders' => Order::count(),
            'total_customers' => User::where('is_admin', false)->count(),
            'total_products' => Product::count(),
            'low_stock_count' => $this->lowStockCount(),
            'order_status' => $this->orderStatusBreakdown(),
            'recent_orders' => $this->recentOrders(),
            'top_products' => $this->topProducts(),
        ]);
    }

    private function lowStockCount(): int
    {
        return Product::query()
            ->withSum('variants', 'stock_quantity')
            ->get()
            ->filter(fn (Product $product) => $product->stock_status === 'low_stock')
            ->count();
    }

    /** Delivered / Shipped / Processing (pending+paid+packed) / Cancelled. */
    private function orderStatusBreakdown(): array
    {
        $counts = Order::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return [
            'delivered' => (int) ($counts['delivered'] ?? 0),
            'shipped' => (int) ($counts['shipped'] ?? 0),
            'processing' => (int) ($counts['pending'] ?? 0) + (int) ($counts['paid'] ?? 0) + (int) ($counts['packed'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function recentOrders(): array
    {
        return Order::query()
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user?->name,
                'total' => (float) $order->total,
                'status' => $order->status,
                'placed_at' => $order->placed_at?->toDateString(),
            ])
            ->all();
    }

    /** Top 5 shoes by units sold, joined via order_items -> product_variants. */
    private function topProducts(): array
    {
        $rows = OrderItem::query()
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->selectRaw('product_variants.product_id, SUM(order_items.quantity) as units_sold, SUM(order_items.line_total) as revenue')
            ->groupBy('product_variants.product_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        $products = Product::whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return $rows->map(fn ($row) => [
            'product_id' => $row->product_id,
            'name' => $products->get($row->product_id)?->name,
            'units_sold' => (int) $row->units_sold,
            'revenue' => (float) $row->revenue,
        ])->all();
    }
}
