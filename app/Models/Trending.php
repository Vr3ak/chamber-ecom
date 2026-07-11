<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An admin-curated "featured" entry for a product. Drives the Trending
 * rail on the homepage and the Trending badge on the product page.
 *
 * @property int  $id
 * @property int  $admin_id
 * @property int  $product_id
 * @property int  $sort_order
 * @property bool $is_active
 */
class Trending extends Model
{
    use HasFactory;

    protected $table = 'trending';

    protected $fillable = ['admin_id', 'product_id', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** Only active, ordered entries. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
