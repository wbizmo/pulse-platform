<?php

namespace App\Services\Operations;

use Illuminate\Support\Str;

final class LogReader
{
    public function files(): array
    {
        $root = realpath(storage_path('logs'));
        if (! $root) {
            return [];
        } $files = [];
        foreach (glob($root.'/laravel*.log') ?: [] as $path) {
            $real = realpath($path);
            if ($real && str_starts_with($real, $root.DIRECTORY_SEPARATOR) && ! is_link($path)) {
                $files[basename($real)] = $real;
            }
        } krsort($files);

        return $files;
    }

    public function read(?string $name, ?string $search): array
    {
        $files = $this->files();
        if (! $name) {
            $name = array_key_first($files);
        } if (! $name || ! isset($files[$name])) {
            return ['file' => null, 'lines' => []];
        } $term = mb_substr(trim((string) $search), 0, 100);
        $max = max(4096, min(1048576, (int) config('operations.log_max_bytes')));
        $handle = fopen($files[$name], 'rb');
        if (! $handle) {
            return ['file' => $name, 'lines' => []];
        } $size = filesize($files[$name]) ?: 0;
        fseek($handle, max(0, $size - $max));
        if ($size > $max) {
            fgets($handle);
        } $content = stream_get_contents($handle, $max);
        fclose($handle);
        $lines = array_slice(preg_split('/\R/', (string) $content) ?: [], -max(1, min(1000, (int) config('operations.log_max_lines'))));
        if ($term !== '') {
            $lines = array_values(array_filter($lines, fn ($line) => Str::contains(mb_strtolower($line), mb_strtolower($term))));
        }

        return ['file' => $name, 'lines' => array_map(fn ($line) => app(Redactor::class)->redact($line), $lines)];
    }
}
