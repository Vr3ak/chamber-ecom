<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single saved product on a wishlist. Table has created_at only (no
 * updated_at) — set explicitly, same treatment as OrderTracking.
 *
 * @property int $id
 * @property int $wishlist_id
 * @property int $product_id
 */
class WishlistItem extends Model
{
    use HasFactory;

    public $timestamps = false; // table has created_at only, set explicitly

    protected $fillable = ['wishlist_id', 'product_id', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
