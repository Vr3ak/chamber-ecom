<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StorePaymentRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\KhqrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Payments — Feature 3. Two methods:
 *
 *   KHQR        -> generates a REAL, scannable Bakong KHQR (KhqrService) and
 *                  stores a PENDING payment. The "paid" step is simulated via
 *                  POST /payments/{payment}/confirm (stands in for the bank
 *                  webhook / Bakong status check).
 *   credit_card -> SIMULATED charge. A reserved test card number is declined
 *                  so a failure can be demoed; any other valid-format card
 *                  succeeds immediately.
 *
 *   POST /api/orders/{order}/pay
 *   POST /api/payments/{payment}/confirm
 */
class PaymentController extends Controller
{
    /** A card number reserved to simulate a decline in the demo. */
    private const DECLINE_TEST_CARD = '4000000000000002';

    public function __construct(private readonly KhqrService $khqr)
    {
    }

    public function pay(StorePaymentRequest $request, Order $order): JsonResponse
    {
        if ($order->isPaid()) {
            return response()->json(['message' => 'This order is already paid.'], 409);
        }

        $data = $request->validated();

        return $data['method'] === 'khqr'
            ? $this->payWithKhqr($order)
            : $this->payWithCard($order, $data);
    }

    /**
     * Simulate the bank/Bakong confirming a KHQR payment.
     * Body: { "result": "success" | "fail" }  (defaults to success)
     */
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

        return $this->respond($payment);
    }

    // -------- method handlers --------

    private function payWithKhqr(Order $order): JsonResponse
    {
        $billRef = 'CH'.$order->id.strtoupper(Str::random(4));
        $payload = $this->khqr->payload((float) $order->total, $billRef);

        $payment = $order->payments()->create([
            'method'          => 'khqr',
            'status'          => 'pending',
            'amount'          => $order->total,
            'currency'        => 'USD',
            // Bakong looks up a dynamic QR's status by the payload's md5.
            'transaction_ref' => $this->khqr->md5($payload),
        ]);

        return response()->json([
            'payment'     => new PaymentResource($payment),
            'qr_string'   => $payload,                              // the real KHQR text
            'qr_image'    => $this->khqr->qrSvgDataUri($payload),   // scannable SVG (data URI)
            'bill_number' => $billRef,
            'next_step'   => 'Scan the QR, then POST /api/payments/'.$payment->id.'/confirm to complete.',
        ], 201);
    }

    private function payWithCard(Order $order, array $data): JsonResponse
    {
        $declined = $data['card_number'] === self::DECLINE_TEST_CARD;

        $payment = $order->payments()->create([
            'method'          => 'credit_card',
            'status'          => $declined ? 'failed' : 'succeeded',
            'amount'          => $order->total,
            'currency'        => 'USD',
            'transaction_ref' => 'CARD-'.strtoupper(Str::random(10)),
            'paid_at'         => $declined ? null : now(),
        ]);

        if (! $declined) {
            $order->update(['status' => 'paid']);
        }

        return $this->respond($payment, $declined ? 402 : 201);
    }

    private function respond(Payment $payment, int $status = 200): JsonResponse
    {
        return response()->json([
            'payment' => new PaymentResource($payment),
            'order'   => new OrderResource($payment->order->fresh()->load(['items', 'payments'])),
        ], $status);
    }
}
