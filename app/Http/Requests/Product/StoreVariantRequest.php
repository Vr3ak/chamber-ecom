<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates adding a single variant to an existing product.
 * Enforces that the colour+size combination is unique per product.
 */
class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'color_id'       => [
                'required', 'integer', Rule::exists('colors', 'id'),
                // guard the unique (product_id, color_id, size_id) combo
                Rule::unique('product_variants', 'color_id')
                    ->where('product_id', $productId)
                    ->where('size_id', $this->input('size_id')),
            ],
            'size_id'        => ['required', 'integer', Rule::exists('sizes', 'id')],
            'price'          => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'color_id.unique' => 'This colour and size combination already exists for the product.',
        ];
    }
}
