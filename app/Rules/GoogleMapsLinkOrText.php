<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleMapsLinkOrText implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $candidate = trim($value);
        if (! preg_match('/^https?:\/\//i', $candidate)) {
            // Plain text location is still allowed.
            return;
        }

        if (! filter_var($candidate, FILTER_VALIDATE_URL)) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        $host = strtolower((string) parse_url($candidate, PHP_URL_HOST));
        $allowedHosts = [
            'maps.app.goo.gl',
            'goo.gl',
            'www.google.com',
            'google.com',
            'maps.google.com',
            'g.co',
        ];

        $isAllowed = false;
        foreach ($allowedHosts as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                $isAllowed = true;
                break;
            }
        }

        if (! $isAllowed) {
            $fail('The :attribute URL must be a Google Maps link.');
        }
    }
}
