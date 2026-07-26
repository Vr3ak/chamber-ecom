<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A star rating (1-5) and optional body left on a product.
 * The product page shows AVG(rating) + COUNT(*) (Advanced SQL doc 1.1).
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int|null $order_id
 * @property int $rating
 * @property string|null $body
 * @property bool $is_verified
 * @property bool $is_hidden
 */
class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'order_id', 'rating', 'body', 'is_verified', 'is_hidden'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Reviews an admin hasn't hidden — what the storefront should ever see. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }
}
