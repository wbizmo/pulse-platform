<?php

namespace App\Domain\Builder;

use App\Domain\Content\MenuLink;
use App\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BuilderDocument
{
    private int $count = 0;

    private array $ids = [];

    public function __construct(private readonly BlockRegistry $registry) {}

    public function decode(string $json): array
    {
        if (strlen($json) > BlockRegistry::MAX_BYTES) {
            $this->fail('The builder document is too large.');
        }
        try {
            $document = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->fail('Builder JSON is invalid.');
        }
        if (! is_array($document) || ! $this->hasExactKeys($document, ['schema_version', 'nodes']) || $document['schema_version'] !== BlockRegistry::SCHEMA_VERSION || ! is_array($document['nodes']) || ! array_is_list($document['nodes'])) {
            $this->fail('The builder document schema or version is unsupported.');
        }
        $this->count = 0;
        $this->ids = [];
        $document['nodes'] = $this->nodes($document['nodes'], 1, false);

        return $document;
    }

    public function empty(): array
    {
        return ['schema_version' => BlockRegistry::SCHEMA_VERSION, 'nodes' => []];
    }

    public function nodeCount(array $document): int
    {
        $walk = function (array $nodes) use (&$walk): int {
            return array_sum(array_map(fn ($node) => 1 + ($node['children'] ? $walk($node['children']) : 0), $nodes));
        };

        return $walk($document['nodes']);
    }

    public function mediaIds(array $document): array
    {
        $ids = [];
        $walk = function (array $nodes) use (&$walk, &$ids): void {
            foreach ($nodes as $node) {
                if ($node['type'] === 'image' && $node['props']['media_id'] !== null) {
                    $ids[] = $node['props']['media_id'];
                } $walk($node['children']);
            }
        };
        $walk($document['nodes']);

        return array_values(array_unique($ids));
    }

    private function nodes(array $nodes, int $depth, bool $parentContainer): array
    {
        if ($depth > BlockRegistry::MAX_DEPTH || count($nodes) > BlockRegistry::MAX_CHILDREN) {
            $this->fail('Builder nesting or child limit exceeded.');
        }
        $result = [];
        foreach ($nodes as $node) {
            if (! is_array($node) || ! $this->hasExactKeys($node, ['id', 'type', 'props', 'settings', 'children'])) {
                $this->fail('A builder node contains unknown or missing fields.');
            }
            if (! is_string($node['id']) || ! Str::isUuid($node['id']) || isset($this->ids[$node['id']])) {
                $this->fail('Builder node IDs must be unique UUIDs.');
            }
            $this->ids[$node['id']] = true;
            if (++$this->count > BlockRegistry::MAX_NODES) {
                $this->fail('Builder node limit exceeded.');
            }
            $definition = $this->registry->definitions()[$node['type']] ?? null;
            if (! $definition) {
                $this->fail('The builder contains an unsupported block type.');
            }
            if ($parentContainer && $node['type'] === 'section') {
                $this->fail('Sections cannot be nested inside another container.');
            }
            if (! is_array($node['props']) || ! is_array($node['settings']) || ! is_array($node['children']) || ! array_is_list($node['children'])) {
                $this->fail('Builder properties, settings, and children must have the expected shapes.');
            }
            $props = $this->props($node['props'], $definition['props']);
            $settings = $this->settings($node['settings'], $definition['responsive']);
            $container = $definition['container'] ?? false;
            if (! $container && $node['children'] !== []) {
                $this->fail('This block type cannot contain children.');
            }
            $result[] = ['id' => $node['id'], 'type' => $node['type'], 'props' => $props, 'settings' => $settings, 'children' => $this->nodes($node['children'], $depth + 1, $container)];
        }

        return $result;
    }

    private function props(array $submitted, array $schema): array
    {
        if (array_diff(array_keys($submitted), array_keys($schema))) {
            $this->fail('A builder block contains an unknown property.');
        }
        $result = [];
        foreach ($schema as $key => $rule) {
            $value = $submitted[$key] ?? ($rule['default'] ?? null);
            if ($rule['type'] === 'string' || $rule['type'] === 'url') {
                if (! is_string($value) || mb_strlen($value) > $rule['max'] || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                    $this->fail("Invalid {$key} property.");
                }
                if ($rule['type'] === 'url' && $value !== '' && ! MenuLink::isSafe($value)) {
                    $this->fail('A builder link is unsafe.');
                }
            } elseif ($rule['type'] === 'enum') {
                if (! is_string($value) || ! in_array($value, $rule['values'], true)) {
                    $this->fail("Invalid {$key} option.");
                }
            } elseif ($rule['type'] === 'media') {
                if ($value !== null && (! is_int($value) || ! Media::query()->whereKey($value)->where('mime_type', 'like', 'image/%')->exists())) {
                    $this->fail('The selected builder image is invalid.');
                }
            } elseif ($rule['type'] === 'video') {
                if (! is_string($value) || ! $this->safeVideo($value)) {
                    $this->fail('Only supported YouTube or Vimeo video URLs are allowed.');
                }
            } elseif ($rule['type'] === 'collection') {
                if (! is_array($value) || ! array_is_list($value) || count($value) > $rule['max']) {
                    $this->fail("Invalid {$key} collection.");
                }
                $value = array_map(fn ($item) => is_array($item) ? $this->props($item, $rule['fields']) : $this->fail("Invalid {$key} item."), $value);
            }
            $result[$key] = $value;
        }

        return $result;
    }

    private function settings(array $submitted, array $schema): array
    {
        if (array_diff(array_keys($submitted), array_keys($schema))) {
            $this->fail('A builder block contains an unknown responsive setting.');
        }
        $result = [];
        foreach ($submitted as $key => $value) {
            if ($key === 'hide_on') {
                if (! is_array($value) || ! array_is_list($value) || count($value) > 3 || array_diff($value, $schema[$key]) || count($value) !== count(array_unique($value))) {
                    $this->fail('Invalid visibility settings.');
                }
            } elseif (! is_string($value) || ! in_array($value, $schema[$key], true)) {
                $this->fail("Invalid {$key} setting.");
            }
            $result[$key] = $value;
        }

        return $result;
    }

    private function safeVideo(string $url): bool
    {
        if ($url === '') {
            return true;
        }
        if (! filter_var($url, FILTER_VALIDATE_URL) || strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            return false;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), ['youtube.com', 'www.youtube.com', 'youtu.be', 'vimeo.com', 'www.vimeo.com'], true);
    }

    private function hasExactKeys(array $value, array $expected): bool
    {
        return count($value) === count($expected)
            && array_diff(array_keys($value), $expected) === [];
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['builder_data' => $message]);
    }
}
