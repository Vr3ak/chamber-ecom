<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates updating an existing variant (colour / size / price / stock).
 */
class UpdateVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'color_id'       => ['sometimes', 'integer', Rule::exists('colors', 'id')],
            'size_id'        => ['sometimes', 'integer', Rule::exists('sizes', 'id')],
            'price'          => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
