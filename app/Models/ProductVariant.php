<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    public const LOW_STOCK_THRESHOLD = 10;

    protected $fillable = ['product_id', 'color_id', 'size_id', 'price', 'stock_quantity'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }


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


    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product?->base_price ?? 0);
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->stock_quantity <= 0 => 'out_of_stock',
            $this->stock_quantity <= self::LOW_STOCK_THRESHOLD => 'low_stock',
            default => 'in_stock',
        };
    }

    public function getVariantLabelAttribute(): string
    {
        return trim(($this->color?->name ?? '').' / '.($this->size?->label ?? ''), ' /');
    }
}
