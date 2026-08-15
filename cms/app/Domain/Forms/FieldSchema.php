<?php

namespace App\Domain\Forms;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class FieldSchema
{
    public const TYPES = ['text', 'email', 'textarea', 'number', 'tel', 'url', 'select', 'radio', 'checkbox', 'date'];

    public const MAX_FIELDS = 50;

    public static function normalize(string $type, array $configuration): array
    {
        $allowed = match ($type) {
            'text', 'textarea', 'tel' => ['min_length', 'max_length'],
            'number' => ['min', 'max'],
            'date' => ['min_date', 'max_date'],
            'select', 'radio' => ['options'],
            default => [],
        };
        if (! in_array($type, self::TYPES, true) || array_diff(array_keys($configuration), $allowed)) {
            throw ValidationException::withMessages(['configuration' => 'The field configuration contains unsupported settings.']);
        }
        if (isset($configuration['options'])) {
            if (! is_array($configuration['options']) || count($configuration['options']) < 1 || count($configuration['options']) > 50) {
                throw ValidationException::withMessages(['configuration' => 'Options must contain between 1 and 50 values.']);
            }
            $configuration['options'] = array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $configuration['options']);
            if (in_array('', $configuration['options'], true) || count(array_unique($configuration['options'])) !== count($configuration['options']) || max(array_map('mb_strlen', $configuration['options'])) > 100) {
                throw ValidationException::withMessages(['configuration' => 'Options must be unique, non-empty, and at most 100 characters.']);
            }
        }
        foreach (['min_length', 'max_length'] as $key) {
            if (isset($configuration[$key]) && (! is_int($configuration[$key]) || $configuration[$key] < 0 || $configuration[$key] > 5000)) {
                throw ValidationException::withMessages(['configuration' => 'Length bounds are invalid.']);
            }
        }
        if (($configuration['min_length'] ?? 0) > ($configuration['max_length'] ?? 5000) || ($configuration['min'] ?? -PHP_INT_MAX) > ($configuration['max'] ?? PHP_INT_MAX) || ($configuration['min_date'] ?? '0000-01-01') > ($configuration['max_date'] ?? '9999-12-31')) {
            throw ValidationException::withMessages(['configuration' => 'Minimum bounds cannot exceed maximum bounds.']);
        }

        return $configuration;
    }

    public static function rules(object $field): array
    {
        $c = $field->configuration ?? [];
        $rules = [$field->required ? 'required' : 'nullable'];
        $rules[] = $field->type === 'checkbox' ? 'boolean' : 'string';
        if ($field->type !== 'checkbox') {
            $rules[] = 'max:'.($c['max_length'] ?? ($field->type === 'textarea' ? 5000 : 500));
        }
        if (isset($c['min_length'])) {
            $rules[] = 'min:'.$c['min_length'];
        }
        if ($field->type === 'email') {
            $rules[] = 'email:rfc';
        }
        if ($field->type === 'url') {
            $rules[] = 'url:http,https';
        }
        if ($field->type === 'number') {
            $rules = [$field->required ? 'required' : 'nullable', 'numeric'];
            if (isset($c['min'])) {
                $rules[] = 'min:'.$c['min'];
            } if (isset($c['max'])) {
                $rules[] = 'max:'.$c['max'];
            }
        }
        if ($field->type === 'date') {
            $rules[] = 'date_format:Y-m-d';
            if (isset($c['min_date'])) {
                $rules[] = 'after_or_equal:'.$c['min_date'];
            } if (isset($c['max_date'])) {
                $rules[] = 'before_or_equal:'.$c['max_date'];
            }
        }
        if (in_array($field->type, ['select', 'radio'], true)) {
            $rules[] = Rule::in($c['options']);
        }

        return $rules;
    }
}
