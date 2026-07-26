<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single product variant + quantity inside a cart. No timestamps in
 * this table; no unique(cart_id, product_variant_id) either — enforced
 * in CartController by incrementing quantity instead of duplicating.
 *
 * @property int $id
 * @property int $cart_id
 * @property int $product_variant_id
 * @property int $quantity
 */
class CartItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['cart_id', 'product_variant_id', 'quantity'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
