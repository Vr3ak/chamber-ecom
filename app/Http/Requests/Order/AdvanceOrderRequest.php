<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route is behind auth:sanctum (admin)
    }

    public function rules(): array
    {
        return [
            'status'          => ['required', 'in:paid,packed,shipped,delivered,cancelled'],
            'note'            => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:60'],
        ];
    }
}