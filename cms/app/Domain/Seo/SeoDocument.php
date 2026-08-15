<?php

namespace App\Domain\Seo;

final readonly class SeoDocument
{
    public function __construct(
        public string $title,
        public string $description,
        public ?string $keywords,
        public ?string $canonical,
        public string $robots,
        public bool $socialEnabled,
        public string $ogType,
        public string $ogTitle,
        public string $ogDescription,
        public ?string $ogImage,
        public string $twitterTitle,
        public string $twitterDescription,
        public ?string $twitterImage,
        public ?array $structuredData,
    ) {}
}
