<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafePublicUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/[\x00-\x1F\x7F]/u', $value)) {
            $fail('The :attribute must be a safe URL.');

            return;
        }
        $parts = parse_url($value);
        if ($parts === false || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || empty($parts['host'])) {
            $fail('The :attribute must be an absolute HTTP or HTTPS URL.');

            return;
        }
        $origin = parse_url(config('app.url'));
        $port = static fn (array $url): int => (int) ($url['port'] ?? (strtolower($url['scheme'] ?? '') === 'https' ? 443 : 80));
        if (strtolower($parts['host']) !== strtolower($origin['host'] ?? '') || $port($parts) !== $port($origin)) {
            $fail('The :attribute must use this site’s public origin.');
        }
    }
}
