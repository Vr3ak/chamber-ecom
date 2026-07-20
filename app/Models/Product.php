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

/**
 * A shoe model (Air Max 90, UltraBoost...). This is the central entity
 * of Feature 1 — the Detailed Product Page.
 *
 * @property int $id
 * @property int $brand_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property float $base_price
 * @property bool $is_active
 */
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

    /**
     * Auto-generate a URL slug from the name when one isn't supplied.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // -------- relationships --------

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

    /** The trending entry for this product, if an admin has featured it. */
    public function trending(): HasOne
    {
        return $this->hasOne(Trending::class);
    }

    // -------- scopes & helpers --------

    /** Only products that should appear in the catalogue. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Use the slug in route-model binding (/api/products/air-max-90). */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Total stock across every variant — the SUM(stock_quantity) from the
     * Advanced SQL doc (1.2). Available as $product->total_stock once
     * withSum('variants', 'stock_quantity') has been eager-loaded.
     */
    public function getTotalStockAttribute(): int
    {
        return (int) ($this->variants_sum_stock_quantity
            ?? $this->variants()->sum('stock_quantity'));
    }

    /** "out_of_stock" / "low_stock" / "in_stock" for the admin shoe list. */
    public function getStockStatusAttribute(): string
    {
        return match (true) {
            $this->total_stock <= 0 => 'out_of_stock',
            $this->total_stock <= ProductVariant::LOW_STOCK_THRESHOLD => 'low_stock',
            default => 'in_stock',
        };
    }

    /** Whether an admin has this product in the active trending set. */
    public function getIsTrendingAttribute(): bool
    {
        if ($this->relationLoaded('trending')) {
            return (bool) ($this->trending?->is_active);
        }

        return $this->trending()->where('is_active', true)->exists();
    }
}
