<?php

namespace App\Domain\Content;

use Illuminate\Support\Str;

final class ReservedSlug
{
    private const VALUES = ['admin', 'api', 'login', 'register', 'registration', 'password', 'forgot-password', 'reset-password', 'verify-email', 'email', 'blog', 'sitemap.xml', 'robots.txt', 'storage', 'build', 'assets', 'plugins', 'themes', 'cart', 'checkout'];

    public static function normalize(string $value): string
    {
        return Str::slug($value);
    }

    public static function contains(string $slug): bool
    {
        return in_array(Str::lower($slug), self::VALUES, true);
    }

    public static function values(): array
    {
        return [...self::VALUES, 'forms'];
    }
}
