<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A category node in a self-referencing tree (Footwear > Sneakers > Running).
 * Feeds the breadcrumb on the detailed product page.
 *
 * @property int         $id
 * @property int|null    $parent_id
 * @property string      $name
 * @property string      $slug
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'slug'];

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (blank($category->slug) && filled($category->name)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Ancestors from root to this node — the breadcrumb trail. */
    public function breadcrumb(): array
    {
        $trail = [];
        $node  = $this;
        while ($node) {
            array_unshift($trail, ['id' => $node->id, 'name' => $node->name, 'slug' => $node->slug]);
            $node = $node->parent;
        }

        return $trail;
    }
}
