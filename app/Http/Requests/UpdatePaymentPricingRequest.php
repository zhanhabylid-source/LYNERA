<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('discount_amount');
        if (is_string($raw)) {
            $normalized = preg_replace('/[^\d.,]/', '', $raw);
            $normalized = str_replace('.', '', (string) $normalized);
            $normalized = str_replace(',', '.', (string) $normalized);
            $this->merge([
                'discount_amount' => $normalized,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

