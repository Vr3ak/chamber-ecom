<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer's saved-for-later list. No timestamps in this table.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 */
class Wishlist extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    /** The user's default wishlist, creating one if none exists. */
    public static function defaultFor(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'My Wishlist'],
        );
    }
}
