<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer's shopping cart. status = active | converted | abandoned.
 * A user may have many carts over time but only one active at once.
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 */
class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** The user's current active cart, creating one if none exists. */
    public static function activeFor(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
        );
    }
}
