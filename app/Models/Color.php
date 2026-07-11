<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A colour option used by the variant picker (Red, Blue, Black...).
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $hex_code   Swatch colour, e.g. #E24B4A
 */
class Color extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'hex_code'];

    /** Variants offered in this colour. */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
