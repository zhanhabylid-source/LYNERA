<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingTermsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_terms_title' => ['required', 'string', 'max:120'],
            'booking_terms_content' => ['required', 'string', 'min:20', 'max:10000'],
        ];
    }
}
