<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is guarded by auth:sanctum + admin
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:5120'],
            'color_id' => ['nullable', 'integer', Rule::exists('colors', 'id')],
            'alt' => ['nullable', 'string', 'max:191'],
            'sort_order' => ['nullable', 'integer'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
