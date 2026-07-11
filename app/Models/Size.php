<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shoe size. `label` is what the shopper sees ("42"); `foot_length_cm`
 * feeds the foot-size guide on the detailed product page.
 *
 * @property int         $id
 * @property string      $label
 * @property float|null  $foot_length_cm
 * @property int         $sort_order
 */
class Size extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'foot_length_cm', 'sort_order'];

    protected function casts(): array
    {
        return [
            'foot_length_cm' => 'decimal:1',
            'sort_order'     => 'integer',
        ];
    }

    /** Variants offered in this size. */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
