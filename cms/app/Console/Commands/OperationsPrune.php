<?php

namespace App\Console\Commands;

use App\Models\OperationsExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class OperationsPrune extends Command
{
    protected $signature = 'operations:prune {--batch=100}';

    protected $description = 'Prune a bounded batch of expired generated exports';

    public function handle(): int
    {
        $limit = max(1, min(500, (int) $this->option('batch')));
        $exports = OperationsExport::where('expires_at', '<=', now())->orderBy('expires_at')->limit($limit)->get();
        $count = 0;
        foreach ($exports as $export) {
            if (str_starts_with($export->path, 'operations/exports/')) {
                Storage::disk('local')->delete($export->path);
            }$export->delete();
            $count++;
        }$this->info("Pruned {$count} expired export(s).");

        return self::SUCCESS;
    }
}
