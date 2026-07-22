<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is guarded by the 'auth' session middleware
    }

    /**
     * Accepts either an explicit variant (product page, colour/size already
     * chosen) or a bare product id (e.g. a wishlist card's "Add to cart"
     * button, which never picked a variant) — exactly one of the two.
     */
    public function rules(): array
    {
        return [
            'product_variant_id' => ['required_without:product_id', 'nullable', 'integer', Rule::exists('product_variants', 'id')],
            'product_id' => ['required_without:product_variant_id', 'nullable', 'integer', Rule::exists('products', 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
