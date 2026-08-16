<?php

namespace App\Console\Commands;

use App\Models\OperationalState;
use Illuminate\Console\Command;

final class OperationsHeartbeat extends Command
{
    protected $signature = 'operations:heartbeat';

    protected $description = 'Record a bounded scheduler heartbeat';

    public function handle(): int
    {
        $start = microtime(true);
        $state = OperationalState::updateOrCreate(['key' => 'scheduler'], ['last_started_at' => now(), 'status' => 'running']);
        $state->update(['last_completed_at' => now(), 'status' => 'healthy', 'duration_ms' => (int) ((microtime(true) - $start) * 1000), 'metadata' => ['source' => 'schedule']]);
        $this->info('Scheduler heartbeat recorded.');

        return self::SUCCESS;
    }
}
