<?php

namespace App\Domain\Builder;

final class BlockRegistry
{
    public const SCHEMA_VERSION = 1;

    public const MAX_BYTES = 131072;

    public const MAX_NODES = 100;

    public const MAX_DEPTH = 4;

    public const MAX_CHILDREN = 24;

    /** @return array<string, array<string, mixed>> */
    public function definitions(): array
    {
        $responsive = ['alignment' => ['left', 'center', 'right'], 'spacing' => ['none', 'sm', 'md', 'lg'], 'width' => ['narrow', 'standard', 'wide'], 'hide_on' => ['mobile', 'tablet', 'desktop']];

        return [
            'section' => ['label' => 'Section', 'container' => true, 'props' => ['variant' => ['type' => 'enum', 'values' => ['plain', 'muted', 'accent'], 'default' => 'plain']], 'responsive' => $responsive],
            'columns' => ['label' => 'Columns', 'container' => true, 'props' => ['layout' => ['type' => 'enum', 'values' => ['equal', 'sidebar-left', 'sidebar-right'], 'default' => 'equal']], 'responsive' => $responsive],
            'hero' => ['label' => 'Hero', 'props' => $this->textProps(['eyebrow' => 120, 'title' => 200, 'description' => 2000, 'button_label' => 120, 'button_url' => ['url', 2048]]), 'responsive' => $responsive],
            'text' => ['label' => 'Text', 'props' => $this->textProps(['content' => 10000]), 'responsive' => $responsive],
            'image' => ['label' => 'Image', 'props' => ['media_id' => ['type' => 'media'], 'alt' => ['type' => 'string', 'max' => 500, 'default' => '']], 'responsive' => $responsive],
            'video' => ['label' => 'Video', 'props' => ['url' => ['type' => 'video', 'default' => '']], 'responsive' => $responsive],
            'cta' => ['label' => 'Call to action', 'props' => $this->textProps(['title' => 200, 'description' => 2000, 'button_label' => 120, 'button_url' => ['url', 2048]]), 'responsive' => $responsive],
            'features' => ['label' => 'Features', 'props' => $this->collectionProps('items', ['title' => 160, 'description' => 1000], ['title' => 200, 'description' => 2000]), 'responsive' => $responsive],
            'stats' => ['label' => 'Statistics', 'props' => $this->collectionProps('items', ['value' => 80, 'label' => 160]), 'responsive' => $responsive],
            'accordion' => ['label' => 'Accordion', 'props' => $this->collectionProps('items', ['question' => 300, 'answer' => 3000], ['title' => 200]), 'responsive' => $responsive],
            'testimonial' => ['label' => 'Testimonial', 'props' => $this->textProps(['quote' => 3000, 'name' => 160, 'role' => 160]), 'responsive' => $responsive],
        ];
    }

    /** @param array<string, int|array{0:string,1:int}> $fields */
    private function textProps(array $fields): array
    {
        return collect($fields)->map(fn ($limit) => is_array($limit) ? ['type' => $limit[0], 'max' => $limit[1], 'default' => ''] : ['type' => 'string', 'max' => $limit, 'default' => ''])->all();
    }

    private function collectionProps(string $name, array $itemFields, array $extra = []): array
    {
        return $this->textProps($extra) + [$name => ['type' => 'collection', 'max' => 12, 'fields' => $this->textProps($itemFields), 'default' => []]];
    }

    public function editorMetadata(): array
    {
        return collect($this->definitions())->map(fn (array $definition, string $type) => ['type' => $type, 'label' => $definition['label'], 'container' => $definition['container'] ?? false, 'defaults' => collect($definition['props'])->map(fn ($prop) => $prop['default'] ?? null)->all()])->values()->all();
    }
}
