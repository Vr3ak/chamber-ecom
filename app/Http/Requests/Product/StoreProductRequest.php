<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creating a shoe (with optional nested variants).
 */
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is guarded by auth:sanctum (admin)
    }

    public function rules(): array
    {
        return [
            'brand_id'    => ['required', 'integer', Rule::exists('brands', 'id')],
            'name'        => ['required', 'string', 'max:180'],
            'slug'        => ['nullable', 'string', 'max:200', 'alpha_dash', Rule::unique('products', 'slug')],
            'description' => ['nullable', 'string'],
            'base_price'  => ['required', 'numeric', 'min:0'],
            'is_active'   => ['boolean'],

            // Optional: attach categories.
            'category_ids'   => ['sometimes', 'array'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')],

            // Optional: create variants in the same request.
            'variants'                  => ['sometimes', 'array'],
            'variants.*.color_id'       => ['required_with:variants', 'integer', Rule::exists('colors', 'id')],
            'variants.*.size_id'        => ['required_with:variants', 'integer', Rule::exists('sizes', 'id')],
            'variants.*.price'          => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required_with:variants', 'integer', 'min:0'],
        ];
    }
}
