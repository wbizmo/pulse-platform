<?php

namespace App\Domain\Plugins;

final class EditorialNoteWidget implements DashboardWidget
{
    public function render(array $settings): array
    {
        return ['title' => 'Editorial note', 'body' => (string) ($settings['message'] ?? ''), 'tone' => $settings['tone'] ?? 'neutral'];
    }
}
