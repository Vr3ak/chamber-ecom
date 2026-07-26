<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A gallery image for a product. May be tied to a colour so the gallery
 * can swap when the shopper changes the colour variant. `is_primary`
 * marks the main thumbnail.
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $color_id
 * @property string $url
 * @property string|null $alt
 * @property int $sort_order
 * @property bool $is_primary
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

    /**
     * Store an uploaded file on the public disk and create its row.
     *
     * @param  array<string, mixed>  $attributes  color_id / alt / sort_order / is_primary
     */
    public static function storeUpload(UploadedFile $file, int $productId, array $attributes = []): self
    {
        $path = $file->storeAs(
            "products/{$productId}",
            Str::uuid().'.'.$file->extension(),
            'public'
        );

        return static::create([
            'product_id' => $productId,
            'color_id' => $attributes['color_id'] ?? null,
            'url' => Storage::disk('public')->url($path),
            'alt' => $attributes['alt'] ?? null,
            'sort_order' => $attributes['sort_order'] ?? 0,
            'is_primary' => $attributes['is_primary'] ?? false,
        ]);
    }
}
