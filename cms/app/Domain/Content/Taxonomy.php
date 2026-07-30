<?php

namespace App\Domain\Content;

use Illuminate\Support\Str;

final class Taxonomy
{
    public const MAX_NAME_LENGTH = 100;

    public const MAX_SLUG_LENGTH = 100;

    public const RESERVED_SLUGS = ['category', 'tag', 'feed', 'page', 'preview'];

    public static function normalizeName(string $name): string
    {
        $normalized = class_exists(\Normalizer::class) ? \Normalizer::normalize($name, \Normalizer::FORM_C) : $name;
        $name = preg_replace('/\s+/u', ' ', trim($normalized ?: $name)) ?? '';

        return Str::lower($name);
    }

    public static function normalizeSlug(?string $slug, string $name): string
    {
        return Str::slug(trim((string) ($slug ?: $name)));
    }
}
