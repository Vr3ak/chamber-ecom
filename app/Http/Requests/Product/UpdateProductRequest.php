<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updating a shoe. All fields optional (PATCH-friendly).
 */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'brand_id'    => ['sometimes', 'required', 'integer', Rule::exists('brands', 'id')],
            'name'        => ['sometimes', 'required', 'string', 'max:180'],
            'slug'        => ['sometimes', 'nullable', 'string', 'max:200', 'alpha_dash',
                              Rule::unique('products', 'slug')->ignore($productId)],
            'description' => ['sometimes', 'nullable', 'string'],
            'base_price'  => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],

            'category_ids'   => ['sometimes', 'array'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')],
        ];
    }
}
