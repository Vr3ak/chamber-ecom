<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderTracking;
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
        $user = User::where('email', 'dara@gmail.com')->first();
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
                'user_id' => $user->id,
                'status' => 'pending',
                'shipping_name' => 'Dara Sok',
                'shipping_phone' => '012345678',
                'shipping_address' => 'St 271, Toul Kork, Phnom Penh, Cambodia',
                'placed_at' => now(),
            ]
        );

        $order->items()->firstOrCreate(
            ['product_variant_id' => $variant->id],
            [
                'product_name' => 'Air Max 90',
                'variant_label' => 'Red / 42',
                'unit_price' => 120.00,
                'quantity' => 2,
                'line_total' => 240.00,
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
        // Feature 4 — fulfilment timeline
        $stages = [
            ['paid',      'Payment confirmed via KHQR',    '2026-06-20 09:25:00'],
            ['packed',    'Packed and ready at warehouse', '2026-06-20 11:00:00'],
            ['shipped',   'Handed to J&T Express courier', '2026-06-21 08:00:00'],
            ['delivered', 'Delivered to customer at door', '2026-06-22 14:30:00'],
        ];
        foreach ($stages as [$status, $note, $at]) {
            OrderTracking::firstOrCreate(
                ['order_id' => $order->id, 'status' => $status],
                ['note' => $note, 'created_at' => $at]
            );
        }

        $notifs = [
            ['order_confirmed', '2026-06-20 09:25:30'],
            ['order_shipped',   '2026-06-21 08:01:00'],
            ['order_delivered', '2026-06-22 14:31:00'],
        ];
        foreach ($notifs as [$type, $sentAt]) {
            Notification::firstOrCreate(
                ['order_id' => $order->id, 'type' => $type],
                ['user_id' => $order->user_id, 'channel' => 'email', 'recipient' => $order->user->email, 'status' => 'sent', 'sent_at' => $sentAt]
            );
        }

        $order->update(['status' => 'delivered', 'tracking_number' => 'KH123456789',
        ]);
    }
}
