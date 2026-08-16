<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OperationalState;
use App\Models\OperationsExport;
use App\Services\Operations\ExportManager;
use App\Services\Operations\HealthManager;
use App\Services\Operations\LogReader;
use App\Services\Operations\QueueInspector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class OperationsController extends Controller
{
    public function overview(HealthManager $health, QueueInspector $queue)
    {
        $results = $health->results();

        return view('admin.operations.overview', ['results' => $results, 'status' => $health->status($results), 'queue' => $queue->summary(), 'scheduler' => OperationalState::where('key', 'scheduler')->first()]);
    }

    public function health(HealthManager $health)
    {
        $results = $health->results();

        return view('admin.operations.health', ['results' => $results, 'status' => $health->status($results)]);
    }

    public function queue(QueueInspector $queue)
    {
        return view('admin.operations.queue', ['summary' => $queue->summary(), 'failures' => $queue->failures()]);
    }

    public function retry(Request $request, string $uuid): RedirectResponse
    {
        abort_unless(DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);
        Artisan::call('queue:retry', [$uuid]);
        $this->recordAudit($request, 'operations.job_retried', $uuid);

        return back()->with('success', 'The failed job was queued for retry.');
    }

    public function forget(Request $request, string $uuid): RedirectResponse
    {
        abort_unless(DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);
        Artisan::call('queue:forget', [$uuid]);
        $this->recordAudit($request, 'operations.job_forgotten', $uuid);

        return back()->with('success', 'The failed job record was removed.');
    }

    public function scheduler()
    {
        return view('admin.operations.scheduler', ['states' => OperationalState::latest('last_completed_at')->paginate(25)]);
    }

    public function logs(Request $request, LogReader $logs)
    {
        $request->validate(['file' => ['nullable', 'string', 'max:100'], 'search' => ['nullable', 'string', 'max:100']]);

        return view('admin.operations.logs', ['files' => array_keys($logs->files()), 'log' => $logs->read($request->string('file')->toString(), $request->string('search')->toString())]);
    }

    public function audit(Request $request)
    {
        $data = $request->validate(['action' => ['nullable', 'string', 'max:100'], 'actor' => ['nullable', 'integer', 'min:1'], 'target_type' => ['nullable', 'string', 'max:100'], 'from' => ['nullable', 'date', 'after_or_equal:'.now()->subDays(93)->toDateString()], 'to' => ['nullable', 'date', 'after_or_equal:from', 'before_or_equal:'.now()->addDay()->toDateString()]]);
        $q = AuditLog::query()->with('actor:id,name')->latest();
        foreach (['action', 'target_type'] as $key) {
            if (! empty($data[$key])) {
                $q->where($key, $data[$key]);
            }
        }if (! empty($data['actor'])) {
            $q->where('actor_id', $data['actor']);
        }if (! empty($data['from'])) {
            $q->whereDate('created_at', '>=', $data['from']);
        }if (! empty($data['to'])) {
            $q->whereDate('created_at', '<=', $data['to']);
        }

        return view('admin.operations.audit', ['audits' => $q->paginate(25)->withQueryString()]);
    }

    public function notifications(Request $request)
    {
        return view('admin.operations.notifications', ['notifications' => $request->user()->notifications()->latest()->paginate(25)]);
    }

    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function exports(Request $request)
    {
        return view('admin.operations.exports', ['exports' => OperationsExport::where('user_id', $request->user()->id)->latest()->paginate(25), 'types' => ExportManager::TYPES]);
    }

    public function createExport(Request $request, ExportManager $manager): RedirectResponse
    {
        $data = $request->validate(['type' => ['required', 'in:'.implode(',', ExportManager::TYPES)]]);
        $export = $manager->create($request->user(), $data['type']);
        $this->recordAudit($request, 'operations.export_requested', $export->id, ['type' => $export->type, 'rows' => $export->row_count]);

        return back()->with('success', 'Private export created.');
    }

    public function download(Request $request, OperationsExport $export): BinaryFileResponse
    {
        abort_unless($export->user_id === $request->user()->id && ! $export->expires_at->isPast() && $export->status === 'ready', 403);
        abort_unless(str_starts_with($export->path, 'operations/exports/') && Storage::disk('local')->exists($export->path), 404);
        $this->recordAudit($request, 'operations.export_downloaded', $export->id, ['type' => $export->type]);

        return response()->download(Storage::disk('local')->path($export->path), $export->type.'-'.$export->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function recordAudit(Request $request, string $action, string $target, array $context = []): void
    {
        AuditLog::create(['actor_id' => $request->user()->id, 'action' => $action, 'target_type' => 'operations', 'target_id' => null, 'context' => array_merge(['reference' => mb_substr($target, 0, 100)], $context), 'ip_address' => $request->ip()]);
    }
}
