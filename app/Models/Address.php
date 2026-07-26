<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A saved shipping address in a customer's address book. Exactly one per
 * user should have `is_default` true — enforced in AddressController, not
 * the schema (no timestamps in this table).
 *
 * @property int $id
 * @property int $user_id
 * @property string $recipient_name
 * @property string $phone
 * @property string $street_line
 * @property string $city
 * @property string|null $province
 * @property string $country
 * @property bool $is_default
 */
class Address extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'recipient_name', 'phone', 'street_line',
        'city', 'province', 'country', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
