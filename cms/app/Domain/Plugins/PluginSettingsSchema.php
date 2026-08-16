<?php

namespace App\Domain\Plugins;

use Illuminate\Validation\ValidationException;

final class PluginSettingsSchema
{
    public function __construct(private PluginManifestRegistry $registry) {}

    public function validate(string $slug, array $input): array
    {
        $schema = $this->registry->get($slug)['settings'] ?? [];
        $unknown = array_diff(array_keys($input), array_keys($schema));
        if ($unknown) {
            throw ValidationException::withMessages(['settings' => 'Unknown plugin setting: '.reset($unknown)]);
        }
        $validated = [];
        foreach ($schema as $key => $rule) {
            $value = $input[$key] ?? $rule['default'] ?? null;
            if (is_array($value) || is_object($value)) {
                throw ValidationException::withMessages([$key => 'The setting must be a scalar value.']);
            }
            $validated[$key] = match ($rule['type']) {
                'boolean' => $this->boolean($value, $key),
                'string' => $this->string($value, $key, $rule['max']),
                'enum' => $this->enum($value, $key, $rule['values']),
                'integer' => $this->integer($value, $key, $rule['min'], $rule['max']),
                default => throw ValidationException::withMessages([$key => 'Unsupported setting type.']),
            };
        }

        return $validated;
    }

    private function boolean(mixed $value, string $key): bool
    {
        if (in_array($value, [true, 1, '1', 'true'], true)) {
            return true;
        }
        if (in_array($value, [false, 0, '0', 'false'], true)) {
            return false;
        }
        throw ValidationException::withMessages([$key => 'The setting must be true or false.']);
    }

    private function string(mixed $value, string $key, int $max): string
    {
        if (! is_string($value) || mb_strlen($value) > $max || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F]/u', $value)) {
            throw ValidationException::withMessages([$key => "The setting must be safe text of at most $max characters."]);
        }

        return $value;
    }

    private function enum(mixed $value, string $key, array $values): string
    {
        if (! is_string($value) || ! in_array($value, $values, true)) {
            throw ValidationException::withMessages([$key => 'The selected setting is invalid.']);
        }

        return $value;
    }

    private function integer(mixed $value, string $key, int $min, int $max): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false || $value < $min || $value > $max) {
            throw ValidationException::withMessages([$key => "The setting must be between $min and $max."]);
        }

        return $value;
    }
}
