<?php

namespace App\Services\Themes;

use App\Models\Theme;

final readonly class ThemeRuntime
{
    public function __construct(public Theme $theme, public array $manifest, public array $settings, public array $media) {}

    public function view(string $document): string
    {
        return 'themes.'.$this->manifest['renderer'].'.'.$document;
    }
}
