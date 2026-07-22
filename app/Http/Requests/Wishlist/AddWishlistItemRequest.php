<?php

namespace App\Http\Requests\Wishlist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddWishlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is guarded by the 'auth' session middleware
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
        ];
    }
}
