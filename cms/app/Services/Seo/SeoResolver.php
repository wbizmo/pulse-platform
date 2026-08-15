<?php

namespace App\Services\Seo;

use App\Domain\Seo\SeoDocument;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Collection;

class SeoResolver
{
    public function resolve(?object $content, Collection $settings, string $url, string $kind = 'page', ?int $pageNumber = null): SeoDocument
    {
        $site = (string) ($settings['site_name'] ?? 'Pulse CMS');
        $baseTitle = trim((string) ($content->meta_title ?? $content->title ?? $settings['seo_default_title'] ?? $site));
        $title = $pageNumber && $pageNumber > 1 ? $baseTitle.' — Page '.$pageNumber : $baseTitle;
        $description = trim((string) ($content->meta_description ?? $content->excerpt ?? $settings['seo_default_description'] ?? $settings['site_tagline'] ?? ''));
        $canonical = $this->canonical($content->canonical_url ?? null, $url, $pageNumber);
        $defaultImage = $this->mediaUrl($settings['seo_default_media_id'] ?? null);
        $featured = $content instanceof Page || $content instanceof Post ? $this->absolute($content->featuredMedia?->public_url) : null;
        $ogImage = $this->safeImage($content->og_image ?? null) ?? $featured ?? $defaultImage;
        $twitterImage = $this->safeImage($content->twitter_image ?? null) ?? $ogImage;
        $ogType = $kind === 'post' ? 'article' : 'website';
        $schema = null;
        if (($settings['seo_schema_enabled'] ?? '1') === '1') {
            $schemaType = in_array($settings['seo_schema_type'] ?? '', ['Organization', 'LocalBusiness', 'Person'], true) ? $settings['seo_schema_type'] : 'WebSite';
            $organizationLogo = $this->mediaUrl($settings['seo_organization_media_id'] ?? null);
            $schema = $kind === 'post' && $content instanceof Post
                ? array_filter(['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $title, 'description' => $description ?: null, 'url' => $canonical, 'image' => $ogImage, 'datePublished' => $content->published_at?->toAtomString(), 'dateModified' => $content->updated_at?->toAtomString(), 'author' => $content->author?->name ? ['@type' => 'Person', 'name' => $content->author->name] : null])
                : array_filter(['@context' => 'https://schema.org', '@type' => $kind === 'site' ? $schemaType : 'WebPage', 'name' => $kind === 'site' ? ($settings['seo_organization_name'] ?? $title) : $title, 'description' => $description ?: null, 'url' => $canonical, 'logo' => $kind === 'site' ? $organizationLogo : null]);
        }

        return new SeoDocument($title, $description, $content->meta_keywords ?? ($settings['seo_default_keywords'] ?? null), ($settings['seo_canonical_enabled'] ?? '1') === '1' ? $canonical : null, ($settings['seo_noindex_enabled'] ?? '0') === '1' ? 'noindex,follow' : 'index,follow', ($settings['seo_social_enabled'] ?? '1') === '1', $ogType, trim((string) ($content->og_title ?? $title)), trim((string) ($content->og_description ?? $description)), $ogImage, trim((string) ($content->twitter_title ?? $title)), trim((string) ($content->twitter_description ?? $description)), $twitterImage, $schema);
    }

    private function canonical(?string $override, string $url, ?int $page): string
    {
        if ($override) {
            return $override;
        }

        return $page && $page > 1 ? $url.(str_contains($url, '?') ? '&' : '?').'page='.$page : $url;
    }

    private function safeImage(?string $url): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?: ''), ['http', 'https'], true) ? $url : null;
    }

    private function mediaUrl(mixed $id): ?string
    {
        if (! is_numeric($id)) {
            return null;
        }

        return $this->absolute(Media::query()->find((int) $id)?->public_url);
    }

    private function absolute(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://') ? $url : url($url);
    }
}
