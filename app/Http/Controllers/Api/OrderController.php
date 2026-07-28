<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->with(['items', 'payments'])
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id'          => $data['user_id'],
                'order_number'     => 'PENDING',
                'status'           => 'pending',
                'shipping_name'    => $data['shipping_name'],
                'shipping_phone'   => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'placed_at'        => now(),
            ]);

            foreach ($data['items'] as $line) {
                $variant = ProductVariant::with(['product', 'color', 'size'])
                    ->lockForUpdate()
                    ->findOrFail($line['product_variant_id']);

                if ($variant->stock_quantity < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Not enough stock for {$variant->product?->name} ({$variant->variant_label}). "
                            ."Only {$variant->stock_quantity} left."],
                    ]);
                }

                $unitPrice = $variant->effective_price;
                $qty       = (int) $line['quantity'];

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product?->name ?? 'Product',
                    'variant_label'      => $variant->variant_label,
                    'unit_price'         => $unitPrice,
                    'quantity'           => $qty,
                    'line_total'         => $unitPrice * $qty,
                ]);

                $variant->decrement('stock_quantity', $qty);
            }

            $order->recalcTotals();
            $order->update(['order_number' => Order::makeOrderNumber($order->id)]);

            return $order;
        });

        return OrderResource::make($order->load(['items', 'payments']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        return OrderResource::make($order->load(['items', 'payments']));
    }

    public function payments(Order $order): AnonymousResourceCollection
    {
        return PaymentResource::collection($order->payments()->orderBy('id')->get());
    }
}