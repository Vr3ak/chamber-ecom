<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:191', Rule::unique('brands', 'name')->ignore($brandId)],
            'slug' => ['sometimes', 'nullable', 'string', 'max:191', 'alpha_dash', Rule::unique('brands', 'slug')->ignore($brandId)],
            'logo_image' => ['sometimes', 'nullable', 'string', 'max:191'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
