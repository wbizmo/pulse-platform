<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class PulseThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Pulse Business',
                'slug' => 'pulse-business',
                'category' => 'business',
                'description' => 'A clean company website theme for agencies, service businesses, consultants, and startups.',
                'is_active' => true,
                'supports' => [
                    'page-builder',
                    'header-builder',
                    'footer-builder',
                    'blog',
                    'forms',
                    'seo',
                    'custom-colors',
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
                'default_settings' => [
                    'primary_color' => '#111827',
                    'accent_color' => '#2563eb',
                    'layout' => 'business',
                ],
            ],
            [
                'name' => 'Pulse Commerce',
                'slug' => 'pulse-commerce',
                'category' => 'ecommerce',
                'description' => 'A storefront theme for products, carts, checkout pages, offers, and payment-focused layouts.',
                'is_active' => false,
                'supports' => [
                    'page-builder',
                    'header-builder',
                    'footer-builder',
                    'products',
                    'cart',
                    'checkout',
                    'payments',
                    'seo',
                ],
                'default_pages' => [
                    'Home',
                    'Shop',
                    'Product Details',
                    'Cart',
                    'Checkout',
                    'Order Success',
                    'Contact',
                    'Refund Policy',
                ],
                'default_settings' => [
                    'primary_color' => '#111827',
                    'accent_color' => '#f97316',
                    'layout' => 'commerce',
                ],
            ],
            [
                'name' => 'Pulse Blog',
                'slug' => 'pulse-blog',
                'category' => 'blog',
                'description' => 'A writing-first theme for blogs, newsletters, editorials, authors, and content-heavy websites.',
                'is_active' => false,
                'supports' => [
                    'blog',
                    'categories',
                    'tags',
                    'authors',
                    'newsletter',
                    'seo',
                    'custom-typography',
                ],
                'default_pages' => [
                    'Home',
                    'Blog',
                    'Post Details',
                    'Categories',
                    'Author Page',
                    'Newsletter',
                    'Contact',
                ],
                'default_settings' => [
                    'primary_color' => '#18181b',
                    'accent_color' => '#7c3aed',
                    'layout' => 'editorial',
                ],
            ],
            [
                'name' => 'Pulse School',
                'slug' => 'pulse-school',
                'category' => 'school',
                'description' => 'An education-focused theme for schools, admissions, courses, staff pages, and events.',
                'is_active' => false,
                'supports' => [
                    'pages',
                    'courses',
                    'events',
                    'gallery',
                    'forms',
                    'seo',
                    'announcements',
                ],
                'default_pages' => [
                    'Home',
                    'About',
                    'Admissions',
                    'Courses',
                    'Events',
                    'Gallery',
                    'Contact',
                ],
                'default_settings' => [
                    'primary_color' => '#0f172a',
                    'accent_color' => '#16a34a',
                    'layout' => 'school',
                ],
            ],
            [
                'name' => 'Pulse Portfolio',
                'slug' => 'pulse-portfolio',
                'category' => 'portfolio',
                'description' => 'A minimal portfolio theme for creators, developers, designers, freelancers, and personal brands.',
                'is_active' => false,
                'supports' => [
                    'page-builder',
                    'projects',
                    'case-studies',
                    'forms',
                    'seo',
                    'custom-colors',
                ],
                'default_pages' => [
                    'Home',
                    'About',
                    'Projects',
                    'Case Studies',
                    'Services',
                    'Contact',
                ],
                'default_settings' => [
                    'primary_color' => '#020617',
                    'accent_color' => '#06b6d4',
                    'layout' => 'portfolio',
                ],
            ],
        ];

        foreach ($themes as $theme) {
            Theme::updateOrCreate(
                ['slug' => $theme['slug']],
                $theme
            );
        }
    }
}
