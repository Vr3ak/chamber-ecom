<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer order (checkout). Feature 3 creates it and attaches payments;
 * Feature 4 walks its status forward and logs tracking + notifications.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $order_number
 * @property string $status
 * @property float  $subtotal
 * @property float  $total
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_number', 'status', 'subtotal', 'total',
        'shipping_name', 'shipping_phone', 'shipping_address',
        'tracking_number', 'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'  => 'decimal:2',
            'total'     => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    // -------- relationships --------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // -------- helpers --------

    /** Recalculate subtotal/total from the current line items. */
    public function recalcTotals(): void
    {
        $subtotal = (float) $this->items()->sum('line_total');
        $this->subtotal = $subtotal;
        $this->total    = $subtotal; // no shipping/tax in this project
        $this->save();
    }

    /** Whether the order has a successful payment. */
    public function isPaid(): bool
    {
        return $this->payments()->where('status', 'succeeded')->exists();
    }

    /** Generate a human order number like CH-2026-0007 from the id. */
    public static function makeOrderNumber(int $id): string
    {
        return 'CH-'.date('Y').'-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }
    public function tracking(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderTracking::class)->orderBy('created_at');
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
