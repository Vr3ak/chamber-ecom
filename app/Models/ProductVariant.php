<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A buyable product + colour + size combination. Carries its own stock
 * and (optionally) its own price. When `price` is null the parent
 * product's base_price applies — the COALESCE(v.price, p.base_price)
 * logic from the Advanced SQL doc.
 *
 * @property int        $id
 * @property int        $product_id
 * @property int        $color_id
 * @property int        $size_id
 * @property float|null $price
 * @property int        $stock_quantity
 */
class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'color_id', 'size_id', 'price', 'stock_quantity'];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    // -------- relationships --------

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    // -------- helpers --------

    /** Effective price = variant price, or the product's base price. */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product?->base_price ?? 0);
    }

    /** Whether this variant can currently be bought. */
    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    /** Display label such as "Red / 42" (used by order_items later). */
    public function getVariantLabelAttribute(): string
    {
        return trim(($this->color?->name ?? '').' / '.($this->size?->label ?? ''), ' /');
    }
}
