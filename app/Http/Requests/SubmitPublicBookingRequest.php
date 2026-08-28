<?php

namespace App\Http\Requests;

use App\Rules\GoogleMapsLinkOrText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPublicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(\+?[0-9][0-9\s\-().]{7,20})$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'service_id' => ['required', 'integer'],
            'service_location' => ['required', Rule::in(['home_service', 'studio'])],
            'people_count' => ['required', 'integer', 'min:1', 'max:20'],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'required_if:service_location,home_service', 'string', 'max:500', new GoogleMapsLinkOrText()],
            'transport_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms_accepted' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp tidak valid. Gunakan format seperti 08xxxxxxxxxx atau +62xxxxxxxxxx.',
            'terms_accepted.required' => 'Anda harus menyetujui Syarat & Ketentuan booking.',
            'terms_accepted.accepted' => 'Anda harus menyetujui Syarat & Ketentuan booking.',
        ];
    }
}
