<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is guarded by auth:sanctum + admin
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('brands', 'name')],
            'slug' => ['nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('brands', 'slug')],
            'logo_image' => ['nullable', 'string', 'max:191'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
