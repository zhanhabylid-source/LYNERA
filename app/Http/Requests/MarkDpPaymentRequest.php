<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkDpPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('dp_amount');
        if (is_string($raw)) {
            // Buang karakter non-numeric selain titik & koma.
            $normalized = preg_replace('/[^\d.,]/', '', $raw);

            // Deteksi format:
            //   - Jika ada koma, itu selalu desimal ala Indonesia -> "." adalah pemisah ribuan.
            //     Contoh: "1.234.567,50" -> hapus titik, ubah koma jadi titik -> "1234567.50"
            //   - Kalau tidak ada koma tapi ada tepat satu titik yang diikuti 1-2 digit di akhir,
            //     itu format desimal en_US ("915000.00") -> biarkan titik sebagai desimal.
            //   - Selain itu, semua titik dianggap pemisah ribuan.
            if (str_contains((string) $normalized, ',')) {
                $normalized = str_replace('.', '', (string) $normalized);
                $normalized = str_replace(',', '.', (string) $normalized);
            } else {
                $dotCount = substr_count((string) $normalized, '.');
                $looksLikeDecimal = $dotCount === 1
                    && (bool) preg_match('/\.\d{1,2}$/', (string) $normalized);
                if (! $looksLikeDecimal) {
                    $normalized = str_replace('.', '', (string) $normalized);
                }
            }

            $this->merge([
                'dp_amount' => $normalized,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'dp_amount' => ['nullable', 'numeric', 'gt:0'],
        ];
    }
}

