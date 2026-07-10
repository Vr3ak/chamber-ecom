<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'color_id'       => ['required', 'integer', Rule::exists('colors', 'id')],
            'size_id'        => ['required', 'integer', Rule::exists('sizes', 'id')],
            // 'sku'            => ['required', 'string', 'max:255', Rule::unique('product_variants', 'sku')],
            'price'          => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],

            'color_id_combo' => [
                Rule::unique('product_variants', 'color_id')
                    ->where('product_id', $productId)
                    ->where('size_id', $this->input('size_id')),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['color_id_combo' => $this->input('color_id')]);
    }

    public function messages(): array
    {
        return [
            'color_id_combo.unique' => 'This colour and size combination already exists for the product.',
        ];
    }

    public function validatedData(): array
    {
        return collect($this->validated())->except('color_id_combo')->all();
    }
}