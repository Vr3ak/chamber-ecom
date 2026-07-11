<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payment attempt on an order (Feature 3). method = khqr | credit_card,
 * status = pending | succeeded | failed | refunded.
 *
 * @property int         $id
 * @property int         $order_id
 * @property string      $method
 * @property string      $status
 * @property float       $amount
 * @property string      $currency
 * @property string|null $transaction_ref
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'method', 'status', 'amount',
        'currency', 'transaction_ref', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
