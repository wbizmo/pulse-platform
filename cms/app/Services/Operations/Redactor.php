<?php

namespace App\Services\Operations;

final class Redactor
{
    private const SENSITIVE = '/(?:pass(?:word)?|authorization|cookie|token|secret|api[_-]?key|client[_-]?secret|recovery[_-]?codes?|mfa|stripe|paypal|flutterwave|paystack)/i';

    public function redact(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && preg_match(self::SENSITIVE, $key)) {
            return '[REDACTED]';
        }
        if (is_array($value)) {
            $redacted = [];
            foreach ($value as $nestedKey => $nestedValue) {
                $redacted[$nestedKey] = $this->redact($nestedValue, (string) $nestedKey);
            }

            return $redacted;
        }
        if (is_string($value)) {
            $value = preg_replace('/\bBearer\s+[A-Za-z0-9._~+\/-]+=*/i', 'Bearer [REDACTED]', $value) ?? $value;

            return preg_replace('/((?:password|authorization|cookie|token|secret|api[_-]?key|client[_-]?secret)\s*[=:]\s*)[^\s,;]+/i', '$1[REDACTED]', $value) ?? $value;
        }

        return $value;
    }
}
