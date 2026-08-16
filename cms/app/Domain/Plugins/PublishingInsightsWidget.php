<?php

namespace App\Domain\Plugins;

use App\Models\Page;

final class PublishingInsightsWidget implements DashboardWidget
{
    public function render(array $settings): array
    {
        return ['title' => 'Publishing insights', 'body' => Page::query()->where('status', 'published')->limit(1001)->count().' published pages', 'tone' => 'neutral'];
    }
}
