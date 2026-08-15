<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Seo\RobotsGenerator;
use App\Services\Seo\SitemapGenerator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoPublicController extends Controller
{
    public function sitemap(SitemapGenerator $generator): Response
    {
        abort_if(Setting::getValue('seo_sitemap_enabled', '1') !== '1', 404);
        $xml = Cache::remember('content.sitemap', 3600, fn () => $generator->generate());

        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(RobotsGenerator $generator): Response
    {
        abort_if(Setting::getValue('seo_robots_enabled', '1') !== '1', 404);

        return response($generator->generate(Setting::getValue('seo_robots_content')))->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
