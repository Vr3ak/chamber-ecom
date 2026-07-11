<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A gallery image for a product. May be tied to a colour so the gallery
 * can swap when the shopper changes the colour variant. `is_primary`
 * marks the main thumbnail.
 *
 * @property int         $id
 * @property int         $product_id
 * @property int|null    $color_id
 * @property string      $url
 * @property string|null $alt
 * @property int         $sort_order
 * @property bool        $is_primary
 */
class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'color_id', 'url', 'alt', 'sort_order', 'is_primary'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
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
}
