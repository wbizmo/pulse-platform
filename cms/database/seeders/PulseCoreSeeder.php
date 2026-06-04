<?php

namespace Database\Seeders;

use App\Models\Plugin;
use App\Models\Theme;
use App\Pulse\Core\Settings;
use Illuminate\Database\Seeder;

class PulseCoreSeeder extends Seeder
{
    public function run(): void
    {
        Settings::set('site_name', 'Pulse CMS', 'general', 'string', true);
        Settings::set('site_tagline', 'A flexible Laravel-powered CMS', 'general', 'string', true);
        Settings::set('site_email', 'hello@example.com', 'contact', 'string', true);
        Settings::set('site_phone', '', 'contact', 'string', true);
        Settings::set('site_address', '', 'contact', 'string', true);

        Settings::set('show_address', true, 'visibility', 'boolean', true);
        Settings::set('show_phone', true, 'visibility', 'boolean', true);
        Settings::set('show_email', true, 'visibility', 'boolean', true);
        Settings::set('show_contact_form', true, 'visibility', 'boolean', true);

        Settings::set('social_links', [
            'facebook' => '',
            'x' => '',
            'instagram' => '',
            'linkedin' => '',
            'youtube' => '',
            'github' => '',
        ], 'social', 'json', true);

        Settings::set('logo', '', 'branding', 'string', true);
        Settings::set('favicon', '', 'branding', 'string', true);
        Settings::set('primary_color', '#2563eb', 'branding', 'string', true);
        Settings::set('secondary_color', '#111827', 'branding', 'string', true);

        Settings::set('active_theme', 'pulse-default', 'theme', 'string', true);
        Settings::set('homepage_id', '', 'reading', 'string', true);
        Settings::set('blog_page_id', '', 'reading', 'string', true);

        Settings::set('custom_admin_url', 'admin', 'security', 'string', false);
        Settings::set('enable_admin_otp', false, 'security', 'boolean', false);
        Settings::set('enable_preloader', true, 'appearance', 'boolean', true);
        Settings::set('maintenance_mode', false, 'system', 'boolean', true);

        Theme::updateOrCreate(
            ['slug' => 'pulse-default'],
            [
                'name' => 'Pulse Default',
                'version' => '1.0.0',
                'author' => 'Pulse CMS',
                'description' => 'A clean default business theme for Pulse CMS.',
                'supports' => [
                    'page-builder',
                    'header-builder',
                    'footer-builder',
                    'blog',
                    'custom-colors',
                    'seo',
                ],
                'default_pages' => [
                    'Home',
                    'About',
                    'Services',
                    'Blog',
                    'Contact',
                    'Privacy Policy',
                    'Terms',
                ],
                'is_active' => true,
            ]
        );

        $plugins = [
            ['Blog', 'blog', 'content', true],
            ['SEO Toolkit', 'seo-toolkit', 'marketing', true],
            ['Security Center', 'security-center', 'security', true],
            ['Site Health', 'site-health', 'system', true],
            ['Error Log Viewer', 'error-log-viewer', 'system', true],
            ['Forms Builder', 'forms-builder', 'forms', true],
            ['Analytics', 'analytics', 'analytics', true],
            ['Popup Builder', 'popup-builder', 'marketing', true],
            ['Backup & Restore', 'backup-restore', 'system', true],
            ['Maintenance Mode', 'maintenance-mode', 'system', true],
            ['Redirect Manager', 'redirect-manager', 'seo', true],
            ['Static HTML Exporter', 'static-html-exporter', 'export', true],
            ['Resend Mailer', 'resend-mailer', 'mail', true],
            ['SMTP Mailer', 'smtp-mailer', 'mail', true],
            ['Stripe Payments', 'stripe-payments', 'payments', false],
            ['Paystack Payments', 'paystack-payments', 'payments', false],
            ['Crypto Payments', 'crypto-payments', 'payments', false],
            ['Ecommerce', 'ecommerce', 'commerce', false],
            ['Business Website', 'business-website', 'site-type', true],
            ['School Website', 'school-website', 'site-type', false],
        ];

        foreach ($plugins as [$name, $slug, $category, $active]) {
            Plugin::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'version' => '1.0.0',
                    'author' => 'Pulse CMS',
                    'description' => $name . ' plugin bundled with Pulse CMS.',
                    'category' => $category,
                    'is_active' => $active,
                    'has_settings' => true,
                ]
            );
        }
    }
}
