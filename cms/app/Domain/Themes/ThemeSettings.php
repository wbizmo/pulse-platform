<?php

namespace App\Domain\Themes;

use App\Models\Media;
use Illuminate\Validation\ValidationException;

class ThemeSettings
{
    public function __construct(private readonly ThemeRegistry $registry) {}

    /** @return array<string, bool|int|string|null> */
    public function validate(string $slug, array $input): array
    {
        $schema = $this->registry->get($slug)['settings'];
        $unknown = array_diff(array_keys($input), array_keys($schema));
        if ($unknown !== []) {
            throw ValidationException::withMessages(['settings' => 'Unknown theme setting: '.reset($unknown).'.']);
        }
        $result = [];
        foreach ($schema as $key => $definition) {
            $value = array_key_exists($key, $input) ? $input[$key] : $definition['default'];
            $result[$key] = $this->normalize($key, $value, $definition);
        }

        return $result;
    }

    public function defaults(string $slug): array
    {
        return $this->validate($slug, []);
    }

    private function normalize(string $key, mixed $value, array $definition): bool|int|string|null
    {
        if (is_array($value) || is_object($value)) {
            throw ValidationException::withMessages([$key => 'The setting must be a scalar value.']);
        }

        return match ($definition['type']) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? throw ValidationException::withMessages([$key => 'The setting must be true or false.']),
            'color' => is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : throw ValidationException::withMessages([$key => 'Use a six-digit hexadecimal colour.']),
            'enum' => is_string($value) && in_array($value, $definition['values'], true) ? $value : throw ValidationException::withMessages([$key => 'Select an allowed value.']),
            'media' => $this->media($key, $value),
            default => throw ValidationException::withMessages([$key => 'Unsupported setting type.']),
        };
    }

    private function media(string $key, mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! ctype_digit((string) $value) || ! Media::query()->whereKey((int) $value)->where('type', 'image')->exists()) {
            throw ValidationException::withMessages([$key => 'Select an available managed image.']);
        }

        return (int) $value;
    }
}
