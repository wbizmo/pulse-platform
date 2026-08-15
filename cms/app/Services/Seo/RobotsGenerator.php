<?php

namespace App\Services\Seo;

class RobotsGenerator
{
    public function generate(?string $custom): string
    {
        $content = trim(str_replace(["\r\n", "\r"], "\n", $custom ?? ''));
        if ($content === '') {
            $content = "User-agent: *\nAllow: /";
        }
        $lines = explode("\n", $content);
        $hasSitemap = false;
        foreach ($lines as &$line) {
            if (preg_match('/^\s*Sitemap\s*:/i', $line)) {
                $hasSitemap = true;
                if (trim(substr($line, strpos($line, ':') + 1)) === '/sitemap.xml') {
                    $line = 'Sitemap: '.route('seo.sitemap');
                }
            }
        }
        if (! $hasSitemap) {
            $lines[] = 'Sitemap: '.route('seo.sitemap');
        }

        return implode("\n", $lines)."\n";
    }
}
