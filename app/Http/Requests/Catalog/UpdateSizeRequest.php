<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sizeId = $this->route('size')?->id;

        return [
            'label' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('sizes', 'label')->ignore($sizeId)],
            'foot_length_cm' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
