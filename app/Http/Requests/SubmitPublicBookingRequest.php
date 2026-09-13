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
            'service_id' => ['nullable', 'integer'],
            'people_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer'],
            'services.*.people_count' => ['required', 'integer', 'min:1', 'max:20'],
            'service_location' => ['required', Rule::in(['home_service', 'studio'])],
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
            'services.required' => 'Minimal satu layanan harus dipilih.',
            'services.min' => 'Minimal satu layanan harus dipilih.',
            'services.*.service_id.required' => 'Layanan wajib dipilih.',
            'services.*.people_count.required' => 'Jumlah orang wajib diisi.',
            'services.*.people_count.min' => 'Jumlah orang minimal 1.',
            'services.*.people_count.max' => 'Jumlah orang maksimal 20 per layanan.',
            'terms_accepted.required' => 'Anda harus menyetujui Syarat & Ketentuan booking.',
            'terms_accepted.accepted' => 'Anda harus menyetujui Syarat & Ketentuan booking.',
        ];
    }
}
