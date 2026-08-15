<?php

namespace App\Services\Seo;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use XMLWriter;

class SitemapGenerator
{
    public const MAX_URLS = 50000;

    public function generate(): string
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $count = 0;
        $this->write($xml, route('frontend.home'));
        $count++;
        $this->write($xml, route('frontend.blog'));
        $count++;

        Page::query()->publiclyVisible()->where('is_homepage', false)->orderBy('id')->select(['id', 'slug', 'updated_at'])->lazyById(500)->each(function (Page $page) use ($xml, &$count): bool {
            if ($count >= self::MAX_URLS) {
                return false;
            }
            $this->write($xml, route('frontend.page', $page->slug), $page->updated_at?->toAtomString());
            $count++;

            return true;
        });
        Post::query()->publiclyVisible()->orderBy('id')->select(['id', 'slug', 'updated_at'])->lazyById(500)->each(function (Post $post) use ($xml, &$count): bool {
            if ($count >= self::MAX_URLS) {
                return false;
            }
            $this->write($xml, route('frontend.blog.show', $post->slug), $post->updated_at?->toAtomString());
            $count++;

            return true;
        });
        foreach ([[Category::class, 'frontend.blog.category'], [Tag::class, 'frontend.blog.tag']] as [$model, $route]) {
            $model::query()->whereHas('posts', fn ($q) => $q->publiclyVisible())->orderBy('id')->select(['id', 'slug', 'updated_at'])->lazyById(500)->each(function ($taxonomy) use ($xml, $route, &$count): bool {
                if ($count >= self::MAX_URLS) {
                    return false;
                }
                $this->write($xml, route($route, $taxonomy->slug), $taxonomy->updated_at?->toAtomString());
                $count++;

                return true;
            });
        }
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function write(XMLWriter $xml, string $location, ?string $modified = null): void
    {
        $xml->startElement('url');
        $xml->writeElement('loc', $location);
        if ($modified) {
            $xml->writeElement('lastmod', $modified);
        }
        $xml->endElement();
    }
}
