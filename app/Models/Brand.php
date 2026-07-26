<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A shoe manufacturer (Nike, Adidas, Puma...).
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $logo_image
 */
class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'logo_image', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Brand $brand): void {
            if (blank($brand->slug) && filled($brand->name)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /** Every product made by this brand. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
