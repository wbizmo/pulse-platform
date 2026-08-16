<?php

namespace App\Domain\Access;

enum Permission: string
{
    case ViewDashboard = 'dashboard.view';
    case ManagePages = 'pages.manage';
    case ManagePosts = 'posts.manage';
    case ManageTaxonomy = 'taxonomy.manage';
    case ManageMedia = 'media.manage';
    case ManageMenus = 'menus.manage';
    case ManageForms = 'forms.manage';
    case ManagePlugins = 'plugins.manage';
    case ManageThemes = 'themes.manage';
    case ManageSeo = 'seo.manage';
    case ManageSettings = 'settings.manage';
    case ManageSystem = 'system.manage';
    case ManageUsers = 'users.manage';
    case ManageRoles = 'roles.manage';
    case ManageCommerceProducts = 'commerce.products.manage';
    case ManageCommerceInventory = 'commerce.inventory.manage';
    case ManageCommerceOrders = 'commerce.orders.manage';
    case ManageCommerceRules = 'commerce.rules.manage';

    public function label(): string
    {
        return match ($this) {
            self::ViewDashboard => 'View dashboard',
            self::ManagePages => 'Manage pages and builder',
            self::ManagePosts => 'Manage posts',
            self::ManageTaxonomy => 'Manage categories and tags',
            self::ManageMedia => 'Manage media',
            self::ManageMenus => 'Manage menus',
            self::ManageForms => 'Manage forms and submissions',
            self::ManagePlugins => 'Manage plugins',
            self::ManageThemes => 'Manage themes',
            self::ManageSeo => 'Manage SEO',
            self::ManageSettings => 'Manage settings',
            self::ManageSystem => 'Run system operations',
            self::ManageUsers => 'Manage users',
            self::ManageRoles => 'Manage roles and permissions',
            self::ManageCommerceProducts => 'Manage commerce catalogue',
            self::ManageCommerceInventory => 'Manage commerce inventory',
            self::ManageCommerceOrders => 'Manage commerce orders',
            self::ManageCommerceRules => 'Manage commerce rules',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
