<?php

namespace App\Domain\Content;

final class MenuLink
{
    public static function isSafe(string $value): bool
    {
        if ($value === '' || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return false;
        }

        if (str_starts_with($value, '/')) {
            return ! str_starts_with($value, '//') && ! str_contains($value, '\\') && ! preg_match('#(?:^|/)\.\.(?:/|$)#', $value);
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
    }
}
