<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PulsePreflight extends Command
{
    protected $signature = 'pulse:preflight';

    protected $description = 'Check whether this host is ready to install Pulse';

    public function handle(): int
    {
        $checks = [
            ['PHP >= 8.3', version_compare(PHP_VERSION, '8.3.0', '>=')],
            ['Application key configured', filled(config('app.key'))],
            ['Production debug disabled', ! app()->environment('production') || ! config('app.debug')],
            ['Storage writable', is_writable(storage_path())],
            ['Bootstrap cache writable', is_writable(base_path('bootstrap/cache'))],
        ];

        foreach (['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pdo', 'session', 'tokenizer', 'xml'] as $extension) {
            $checks[] = ["Extension: {$extension}", extension_loaded($extension)];
        }

        try {
            DB::connection()->getPdo();
            $checks[] = ['Database connection', true];
        } catch (\Throwable) {
            $checks[] = ['Database connection', false];
        }

        $this->table(['Check', 'Result'], array_map(fn (array $check) => [$check[0], $check[1] ? 'PASS' : 'FAIL'], $checks));

        if (in_array(false, array_column($checks, 1), true)) {
            $this->error('Preflight failed. Correct every failed check before installation.');

            return self::FAILURE;
        }

        $this->info('Preflight passed. Cache, session, queue and mail drivers: '.implode(', ', [config('cache.default'), config('session.driver'), config('queue.default'), config('mail.default')]));

        return self::SUCCESS;
    }
}
