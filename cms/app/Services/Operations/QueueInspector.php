<?php

namespace App\Services\Operations;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class QueueInspector
{
    public function summary(): array
    {
        $database = config('queue.connections.'.config('queue.default').'.driver') === 'database';
        if (! $database) {
            return ['driver' => (string) config('queue.connections.'.config('queue.default').'.driver'), 'pending' => null, 'failed' => null, 'oldest_age' => null, 'recent_failure' => null];
        } $oldest = DB::table('jobs')->min('created_at');

        return ['driver' => 'database', 'pending' => DB::table('jobs')->count(), 'failed' => DB::table('failed_jobs')->count(), 'oldest_age' => $oldest ? now()->timestamp - $oldest : null, 'recent_failure' => DB::table('failed_jobs')->max('failed_at')];
    }

    public function failures(): LengthAwarePaginator
    {
        return DB::table('failed_jobs')->select(['id', 'uuid', 'connection', 'queue', 'failed_at', 'payload', 'exception'])->latest('failed_at')->paginate(25)->through(fn ($row) => ['id' => $row->id, 'uuid' => $row->uuid, 'connection' => mb_substr($row->connection, 0, 80), 'queue' => mb_substr($row->queue, 0, 80), 'failed_at' => $row->failed_at, 'job' => $this->jobName($row->payload), 'summary' => $this->failureSummary($row->exception)]);
    }

    private function jobName(string $payload): string
    {
        $data = json_decode($payload, true);
        $name = is_array($data) ? ($data['displayName'] ?? 'Unknown job') : 'Unknown job';

        return mb_substr(preg_replace('/[^A-Za-z0-9_\\.-]/', '', (string) $name) ?: 'Unknown job', 0, 160);
    }

    private function failureSummary(string $exception): string
    {
        return mb_substr(app(Redactor::class)->redact(strtok(str_replace(["\r", "\n"], ' ', $exception), "\n")), 0, 240);
    }
}
