<?php

namespace App\Services\Operations;

use App\Models\AuditLog;
use App\Models\OperationsExport;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ExportManager
{
    public const TYPES = ['orders', 'payments', 'products', 'audit'];

    public function create(User $user, string $type): OperationsExport
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Unsupported export type.');
        }
        $export = OperationsExport::create(['id' => (string) Str::uuid(), 'user_id' => $user->id, 'type' => $type, 'path' => 'operations/exports/'.Str::uuid().'.csv', 'expires_at' => now()->addHours(max(1, min(168, (int) config('operations.export_retention_hours'))))]);
        $handle = fopen('php://temp/maxmemory:2097152', 'w+b');
        fwrite($handle, "\xEF\xBB\xBF");
        $rows = 0;
        $max = max(1, min(50000, (int) config('operations.export_max_rows')));
        [$headers,$query,$map] = $this->definition($type);
        fputcsv($handle, $headers);
        foreach ($query->lazyById(200) as $model) {
            if ($rows >= $max) {
                break;
            } fputcsv($handle, array_map([$this, 'cell'], $map($model)));
            $rows++;
        }
        rewind($handle);
        Storage::disk('local')->put($export->path, $handle);
        fclose($handle);
        $export->update(['row_count' => $rows, 'status' => 'ready']);

        return $export->refresh();
    }

    private function definition(string $type): array
    {
        return match ($type) {
            'orders' => [['reference', 'state', 'currency', 'total_minor', 'created_at'], Order::query()->select(['id', 'reference', 'state', 'currency', 'total_minor', 'created_at']), fn ($m) => [$m->reference, $m->state->value, $m->currency, $m->total_minor, $m->created_at?->toIso8601String()]],
            'payments' => [['id', 'order_id', 'gateway', 'state', 'currency', 'amount_minor', 'created_at'], Payment::query()->select(['id', 'order_id', 'gateway', 'state', 'currency', 'amount_minor', 'created_at']), fn ($m) => [$m->id, $m->order_id, $m->gateway, $m->state->value, $m->currency, $m->amount_minor, $m->created_at?->toIso8601String()]],
            'products' => [['id', 'name', 'slug', 'state', 'created_at'], Product::query()->select(['id', 'name', 'slug', 'state', 'created_at']), fn ($m) => [$m->id, $m->name, $m->slug, $m->state->value, $m->created_at?->toIso8601String()]],
            'audit' => [['id', 'actor_id', 'action', 'target_type', 'target_id', 'created_at'], AuditLog::query()->select(['id', 'actor_id', 'action', 'target_type', 'target_id', 'created_at']), fn ($m) => [$m->id, $m->actor_id, $m->action, $m->target_type, $m->target_id, $m->created_at?->toIso8601String()]],
        };
    }

    public function cell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/u', $value) ? "'".$value : $value;
    }
}
