<?php

namespace App\Console\Commands;

use App\Domain\Content\ContentStatus;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled {--batch=100}';

    protected $description = 'Atomically publish due scheduled pages and posts';

    public function handle(): int
    {
        $total = 0;
        foreach ([Page::class, Post::class] as $model) {
            $ids = $model::query()->where('status', ContentStatus::Scheduled)->where('published_at', '<=', now())->orderBy('published_at')->limit(max(1, min(1000, (int) $this->option('batch'))))->pluck('id');
            foreach ($ids as $id) {
                try {
                    $updated = $model::query()->whereKey($id)->where('status', ContentStatus::Scheduled)->where('published_at', '<=', now())->update(['status' => ContentStatus::Published, 'lock_version' => DB::raw('lock_version + 1')]);
                    if ($updated === 1) {
                        $total++;
                        Cache::forget('content.sitemap');
                        AuditLog::create(['action' => 'content.published_scheduled', 'target_type' => (new $model)->getMorphClass(), 'target_id' => $id, 'context' => ['source' => 'scheduler']]);
                    }
                } catch (\Throwable $exception) {
                    Log::error('Scheduled content publication failed.', ['model' => $model, 'id' => $id, 'exception' => $exception]);
                }
            }
        }
        $this->info("Published {$total} content item(s).");

        return self::SUCCESS;
    }
}
