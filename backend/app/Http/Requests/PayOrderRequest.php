<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Id del PaymentMethod de Stripe generado en el frontend (ej. "pm_card_visa" en modo prueba).
            'payment_method' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Debes indicar un método de pago de Stripe.',
        ];
    }
}
