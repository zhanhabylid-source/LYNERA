<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Rules\GoogleMapsLinkOrText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(fn (Builder $query) => $query->where('tenant_id', auth()->id())),
            ],
            'service_id' => [
                'nullable',
                'integer',
                'required_without:services',
                Rule::exists('services', 'id')->where(fn (Builder $query) => $query->where('tenant_id', auth()->id())),
            ],
            'services' => ['nullable', 'array', 'required_without:service_id', 'min:1'],
            'services.*.service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(fn (Builder $query) => $query->where('tenant_id', auth()->id())),
            ],
            'services.*.people_count' => ['required', 'integer', 'min:1', 'max:50'],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:500', new GoogleMapsLinkOrText()],
            'transport_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => [
                'required',
                Rule::in([
                    Booking::STATUS_PENDING,
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_COMPLETED,
                    Booking::STATUS_CANCELED,
                ]),
            ],
            'notes' => ['nullable', 'string'],
        ];
    }
}
