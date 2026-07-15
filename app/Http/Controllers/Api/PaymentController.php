<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\KhqrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 *   POST /api/orders/{order}/pay          create a real KHQR + pending payment
 *   POST /api/payments/{payment}/confirm  simulate the Bakong "paid" callback
 */
class PaymentController extends Controller
{
    public function __construct(private readonly KhqrService $khqr)
    {
    }

    public function pay(Order $order): JsonResponse
    {
        if ($order->isPaid()) {
            return response()->json(['message' => 'This order is already paid.'], 409);
        }

        $billRef = 'CH'.$order->id.strtoupper(Str::random(4));
        $payload = $this->khqr->payload((float) $order->total, $billRef);

        $payment = $order->payments()->create([
            'method'          => 'khqr',
            'status'          => 'pending',
            'amount'          => $order->total,
            'currency'        => 'USD',
            'transaction_ref' => $this->khqr->md5($payload),
        ]);

        return response()->json([
            'payment'     => new PaymentResource($payment),
            'qr_string'   => $payload,
            'qr_image'    => $this->khqr->qrSvgDataUri($payload),
            'bill_number' => $billRef,
            'next_step'   => 'Scan the QR, then POST /api/payments/'.$payment->id.'/confirm to complete.',
        ], 201);
    }

    public function confirm(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->method !== 'khqr' || $payment->status !== 'pending') {
            return response()->json(['message' => 'This payment cannot be confirmed.'], 409);
        }

        if ($request->input('result', 'success') === 'fail') {
            $payment->update(['status' => 'failed']);
        } else {
            $payment->update(['status' => 'succeeded', 'paid_at' => now()]);
            $payment->order->update(['status' => 'paid']);
        }

        return response()->json([
            'payment' => new PaymentResource($payment),
            'order'   => new OrderResource($payment->order->fresh()->load(['items', 'payments'])),
        ]);
    }
}