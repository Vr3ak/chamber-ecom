<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

/**
 * Sends an email notification for an order event and records it in the
 * notifications table. Uses whatever SMTP is configured (Mailtrap in dev).
 */
class NotificationService
{
    public function notify(Order $order, string $type): Notification
    {
        $user      = $order->user;
        $recipient = (string) ($user?->email);

        $status = 'queued';

        try {
            Mail::raw($this->body($order, $type), function ($m) use ($recipient, $type) {
                $m->to($recipient)->subject($this->subject($type));
            });
            $status = 'sent';
        } catch (\Throwable $e) {
            report($e);
            $status = 'failed';
        }

        return $order->notifications()->create([
            'user_id'   => $user?->id,
            'channel'   => 'email',
            'type'      => $type,
            'recipient' => $recipient,
            'status'    => $status,
            'sent_at'   => $status === 'sent' ? now() : null,
        ]);
    }

    private function subject(string $type): string
    {
        return match ($type) {
            'order_confirmed' => 'Chamber — your order is confirmed',
            'order_shipped'   => 'Chamber — your order has shipped',
            'order_delivered' => 'Chamber — your order was delivered',
            default           => 'Chamber — order update',
        };
    }

    private function body(Order $order, string $type): string
    {
        $name   = $order->shipping_name;
        $number = $order->order_number;

        return match ($type) {
            'order_confirmed' => "Hi {$name}, your Chamber order {$number} is confirmed. "
                ."Total: \${$order->total}. We'll let you know when it ships.",
            'order_shipped'   => "Your Chamber order {$number} has shipped"
                .($order->tracking_number ? " (tracking {$order->tracking_number})" : '').'.',
            'order_delivered' => "Your Chamber order {$number} was delivered. Thanks for shopping with Chamber!",
            default           => "Update on your Chamber order {$number}.",
        };
    }
}