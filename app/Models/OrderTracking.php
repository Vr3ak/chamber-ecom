<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row in an order's fulfilment timeline (Feature 4): paid, packed,
 * shipped, delivered... Append-only; the order_tracking table has only a
 * created_at (no updated_at).
 *
 * @property int $id
 * @property int $order_id
 * @property string $status
 * @property string|null $note
 */
class OrderTracking extends Model
{
    use HasFactory;

    protected $table = 'order_tracking';

    public $timestamps = false; // table has created_at only, set explicitly

    protected $fillable = ['order_id', 'status', 'note', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
