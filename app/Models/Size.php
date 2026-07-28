<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
