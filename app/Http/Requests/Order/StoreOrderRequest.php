<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates placing an order (checkout). `user_id` is accepted while
 * customer auth is being wired up — in production it comes from auth().
 */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'                  => ['required', 'integer', Rule::exists('users', 'id')],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'shipping_name'            => ['required', 'string', 'max:120'],
            'shipping_phone'           => ['required', 'string', 'max:30'],
            'shipping_address'         => ['required', 'string', 'max:255'],
        ];
    }
}
