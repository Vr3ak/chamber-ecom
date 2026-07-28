<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\KhqrService;
use App\Services\OrderTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CheckoutPaymentController extends Controller
{
    public function __construct(
        private readonly KhqrService $khqr,
        private readonly OrderTrackingService $tracking,
    ) {
    }

    public function show(Request $request, Order $order): Response|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        if ($order->isPaid()) {
            return to_route('track', ['order' => $order->order_number]);
        }

        $payload = $this->khqr->payload((float) $order->total, 'CH'.$order->id);

        $payment = $order->payments()
            ->where('method', 'khqr')
            ->where('status', 'pending')
            ->latest('id')
            ->first()
            ?? $order->payments()->create([
                'method' => 'khqr',
                'status' => 'pending',
                'amount' => $order->total,
                'currency' => 'USD',
                'transaction_ref' => $this->khqr->md5($payload),
            ]);

        return inertia('checkout/payment', [
            'order' => [
                'order_number' => $order->order_number,
                'subtotal' => (float) $order->subtotal,
                'total' => (float) $order->total,
            ],
            'payment' => [
                'id' => $payment->id,
                'transaction_ref' => $payment->transaction_ref,
            ],
            'qrImage' => $this->khqr->qrSvgDataUri($payload),
            'merchant' => config('services.khqr.merchant_name', 'Chamber'),
        ]);
    }

    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->order->user_id === $request->user()->id, 404);
        abort_unless($payment->method === 'khqr' && $payment->status === 'pending', 409);

        if ($request->input('result') === 'fail') {
            $payment->update(['status' => 'failed']);

            return back()->withErrors(['payment' => 'Payment failed. Scan the QR and try again.']);
        }

        $payment->update(['status' => 'succeeded', 'paid_at' => now()]);
        $this->tracking->advance($payment->order, 'paid', 'Payment received.');

        return to_route('checkout.confirmation', $payment->order);
    }
}
