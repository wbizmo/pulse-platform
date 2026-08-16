<?php

namespace App\Console\Commands;

use App\Models\Installation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PulseStatus extends Command
{
    protected $signature = 'pulse:status';

    protected $description = 'Report installation state without exposing secrets';

    public function handle(): int
    {
        if (! Schema::hasTable('installations') || ! ($installation = Installation::query()->first())) {
            $this->warn('Pulse is not installed.');

            return self::FAILURE;
        }

        $this->info("Pulse is installed (release {$installation->release}, completed {$installation->completed_at->toIso8601String()}).");

        return self::SUCCESS;
    }
}
