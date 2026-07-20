<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'parent_id' => [
                'sometimes', 'nullable', 'integer', Rule::exists('categories', 'id'),
                Rule::notIn([$categoryId]),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:140', 'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($categoryId)],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.not_in' => 'A category cannot be its own parent.',
        ];
    }
}
