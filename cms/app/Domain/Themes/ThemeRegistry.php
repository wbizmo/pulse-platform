<?php

namespace App\Domain\Themes;

use InvalidArgumentException;

class ThemeRegistry
{
    public const FALLBACK = 'pulse-studio';

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        $common = [
            'version' => '1.0.0', 'manifest_version' => 1, 'settings_schema_version' => 1,
            'compatibility' => ['builder_schema' => 1, 'pulse' => '^1.0'],
            'capabilities' => ['pages', 'builder-v4', 'posts', 'taxonomies', 'forms', 'menus', 'seo'],
            'settings' => [
                'logo_media_id' => ['type' => 'media', 'default' => null, 'group' => 'Brand'],
                'favicon_media_id' => ['type' => 'media', 'default' => null, 'group' => 'Brand'],
                'primary_color' => ['type' => 'color', 'default' => '#172554', 'group' => 'Colour'],
                'accent_color' => ['type' => 'color', 'default' => '#2563eb', 'group' => 'Colour'],
                'typography' => ['type' => 'enum', 'values' => ['system', 'editorial', 'geometric'], 'default' => 'system', 'group' => 'Typography'],
                'density' => ['type' => 'enum', 'values' => ['compact', 'comfortable', 'spacious'], 'default' => 'comfortable', 'group' => 'Layout'],
                'header_variant' => ['type' => 'enum', 'values' => ['minimal', 'classic', 'centered'], 'default' => 'classic', 'group' => 'Layout'],
                'footer_variant' => ['type' => 'enum', 'values' => ['compact', 'columns'], 'default' => 'columns', 'group' => 'Layout'],
                'button_radius' => ['type' => 'enum', 'values' => ['square', 'soft', 'pill'], 'default' => 'soft', 'group' => 'Controls'],
                'content_width' => ['type' => 'enum', 'values' => ['narrow', 'standard', 'wide'], 'default' => 'standard', 'group' => 'Layout'],
                'show_back_to_top' => ['type' => 'boolean', 'default' => true, 'group' => 'Controls'],
            ],
        ];

        return [
            'pulse-studio' => $common + ['slug' => 'pulse-studio', 'name' => 'Pulse Studio', 'description' => 'A vivid editorial canvas for creative teams, portfolios and service stories.', 'category' => 'creative', 'renderer' => 'studio', 'screenshot' => 'themes/studio.svg'],
            'pulse-corporate' => array_replace_recursive($common, ['slug' => 'pulse-corporate', 'name' => 'Pulse Corporate', 'description' => 'A restrained, credible presentation for institutions and professional organisations.', 'category' => 'business', 'renderer' => 'corporate', 'screenshot' => 'themes/corporate.svg', 'settings' => ['primary_color' => ['default' => '#0f2942'], 'accent_color' => ['default' => '#0f766e'], 'button_radius' => ['default' => 'square']]]),
            'pulse-commerce' => array_replace_recursive($common, ['slug' => 'pulse-commerce', 'name' => 'Pulse Commerce', 'description' => 'A content-first commerce-ready visual system that remains complete before catalogue modules arrive.', 'category' => 'commerce', 'renderer' => 'commerce', 'screenshot' => 'themes/commerce.svg', 'capabilities' => [...$common['capabilities'], 'commerce-slots'], 'settings' => ['primary_color' => ['default' => '#18181b'], 'accent_color' => ['default' => '#ea580c'], 'button_radius' => ['default' => 'pill']]]),
        ];
    }

    public function get(string $slug): array
    {
        return $this->all()[$slug] ?? throw new InvalidArgumentException('Unknown or retired theme.');
    }

    public function compatible(array $manifest): bool
    {
        return ($manifest['manifest_version'] ?? null) === 1 && ($manifest['settings_schema_version'] ?? null) === 1 && ($manifest['compatibility']['builder_schema'] ?? null) === 1;
    }
}
