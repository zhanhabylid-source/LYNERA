<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicBookingFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => [
                'required',
                'integer',
                Rule::exists('services', 'id')
                    ->where(fn (Builder $query) => $query->where('tenant_id', auth()->id())),
            ],
            'max_submissions' => ['nullable', 'integer', 'min:1', 'max:500'],
            'transport_fee' => ['nullable', 'numeric', 'min:0'],
            'terms_title' => ['required', 'string', 'max:120'],
            'terms_content' => ['required', 'string', 'min:20', 'max:10000'],
        ];
    }
}
