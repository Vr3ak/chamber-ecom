<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the Feature 3 sample order from the Mission 5 doc:
 * order CH-2026-0001 for Dara, 2x Air Max 90 (Red/42) = $240, with a
 * payment history of a failed KHQR followed by a successful credit card.
 */
class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user    = User::where('email', 'dara@gmail.com')->first();
        $product = Product::where('slug', 'air-max-90')->first();

        if (! $user || ! $product) {
            return;
        }

        $variant = $product->variants()
            ->whereHas('color', fn ($q) => $q->where('name', 'Red'))
            ->whereHas('size', fn ($q) => $q->where('label', '42'))
            ->first();

        if (! $variant) {
            return;
        }

        $order = Order::firstOrCreate(
            ['order_number' => 'CH-2026-0001'],
            [
                'user_id'          => $user->id,
                'status'           => 'pending',
                'shipping_name'    => 'Dara Sok',
                'shipping_phone'   => '012345678',
                'shipping_address' => 'St 271, Toul Kork, Phnom Penh, Cambodia',
                'placed_at'        => now(),
            ]
        );

        $order->items()->firstOrCreate(
            ['product_variant_id' => $variant->id],
            [
                'product_name'  => 'Air Max 90',
                'variant_label' => 'Red / 42',
                'unit_price'    => 120.00,
                'quantity'      => 2,
                'line_total'    => 240.00,
            ]
        );

        $order->recalcTotals();

        // Payment history: failed KHQR, then successful card (never overwritten).
        $order->payments()->firstOrCreate(
            ['transaction_ref' => 'KHQR-TIMEOUT'],
            ['method' => 'khqr', 'status' => 'failed', 'amount' => 240.00, 'currency' => 'USD']
        );
        $order->payments()->firstOrCreate(
            ['transaction_ref' => 'CARD-VISA-9931'],
            ['method' => 'credit_card', 'status' => 'succeeded', 'amount' => 240.00, 'currency' => 'USD', 'paid_at' => now()]
        );

        $order->update(['status' => 'paid']);
    }
}