<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    Payment::STATUS_PENDING,
                    Payment::STATUS_PAID,
                    Payment::STATUS_FAILED,
                ]),
            ],
            'payment_method' => ['required', 'string', 'max:50'],
        ];
    }
}
