<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $cart = Cart::activeFor($request->user())
            ->load(['items.variant.color', 'items.variant.size', 'items.variant.product.images']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show');
        }

        return Inertia::render('checkout/index', [
            'cart' => CartResource::make($cart)->resolve(),
            'addresses' => AddressResource::collection(
                $request->user()->addresses()->orderByDesc('is_default')->get()
            )->resolve(),
            'user' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:32'],
            'street_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country' => ['required', 'string', 'max:120'],
        ]);

        $cart = Cart::activeFor($request->user())->load('items.variant.product');

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Your cart is empty.'],
            ]);
        }

        $address = collect([
            $data['street_line'],
            $data['city'],
            $data['province'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'],
        ])->filter()->implode(', ');

        $order = DB::transaction(function () use ($cart, $data, $address, $request) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => 'PENDING',
                'status' => 'pending',
                'shipping_name' => $data['shipping_name'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_address' => $address,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $variant = ProductVariant::with(['product', 'color', 'size'])
                    ->lockForUpdate()
                    ->findOrFail($item->product_variant_id);

                if ($variant->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => ["Not enough stock for {$variant->product?->name} ({$variant->variant_label}). "
                            ."Only {$variant->stock_quantity} left."],
                    ]);
                }

                $unitPrice = $variant->effective_price;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product?->name ?? 'Product',
                    'variant_label' => $variant->variant_label,
                    'unit_price' => $unitPrice,
                    'quantity' => $item->quantity,
                    'line_total' => $unitPrice * $item->quantity,
                ]);

                $variant->decrement('stock_quantity', $item->quantity);
            }

            $order->recalcTotals();
            $order->update(['order_number' => Order::makeOrderNumber($order->id)]);

            $cart->items()->delete();

            return $order;
        });

        return redirect()->route('checkout.pay', $order);
    }

    public function confirmation(Request $request, Order $order): Response
    {
        abort_if($order->user_id !== $request->user()->id, 404);

        return Inertia::render('checkout/confirmation', [
            'order' => OrderResource::make($order->load(['items', 'payments']))->resolve(),
        ]);
    }
}
