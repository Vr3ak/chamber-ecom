<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a payment attempt. Card fields are only required when paying
 * by credit card. Card data is validated for format but never stored.
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method'      => ['required', 'in:khqr,credit_card'],

            'card_name'   => ['required_if:method,credit_card', 'string', 'max:120'],
            'card_number' => ['required_if:method,credit_card', 'string', 'regex:/^\d{13,19}$/'],
            'card_expiry' => ['required_if:method,credit_card', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'card_cvv'    => ['required_if:method,credit_card', 'string', 'regex:/^\d{3,4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.regex' => 'Card number must be 13 to 19 digits.',
            'card_expiry.regex' => 'Expiry must be in MM/YY format.',
            'card_cvv.regex'    => 'CVV must be 3 or 4 digits.',
        ];
    }
}
