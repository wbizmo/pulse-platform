<?php

namespace App\Domain\Plugins;

interface DashboardWidget
{
    public function render(array $settings): array;
}
