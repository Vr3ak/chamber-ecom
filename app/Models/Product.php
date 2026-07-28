<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id', 'name', 'slug', 'description', 'base_price', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }


    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function trending(): HasOne
    {
        return $this->hasOne(Trending::class);
    }


    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getTotalStockAttribute(): int
    {
        return (int) ($this->variants_sum_stock_quantity
            ?? $this->variants()->sum('stock_quantity'));
    }

    public function getStockStatusAttribute(): string
    {
        return match (true) {
            $this->total_stock <= 0 => 'out_of_stock',
            $this->total_stock <= ProductVariant::LOW_STOCK_THRESHOLD => 'low_stock',
            default => 'in_stock',
        };
    }

    public function getIsTrendingAttribute(): bool
    {
        if ($this->relationLoaded('trending')) {
            return (bool) ($this->trending?->is_active);
        }

        return $this->trending()->where('is_active', true)->exists();
    }

    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants()->where('stock_quantity', '>', 0)->orderBy('id')->first()
            ?? $this->variants()->orderBy('id')->first();
    }
}
