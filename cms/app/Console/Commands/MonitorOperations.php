<?php

namespace App\Console\Commands;

use App\Domain\Access\Permission;
use App\Domain\Operations\HealthStatus;
use App\Models\User;
use App\Notifications\OperationalAlert;
use App\Services\Operations\HealthManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class MonitorOperations extends Command
{
    protected $signature = 'operations:monitor';

    protected $description = 'Evaluate health transitions and send deduplicated operator notifications';

    public function handle(HealthManager $health): int
    {
        $results = $health->results();
        $status = $health->status($results);
        $previous = Cache::get('pulse:operations:health:last-status');
        Cache::put('pulse:operations:health:last-status', $status->value, now()->addDays(7));

        if ($status->value === $previous || ($previous === null && $status === HealthStatus::Healthy)) {
            return self::SUCCESS;
        }

        $problem = $status !== HealthStatus::Healthy;
        $title = $problem ? 'Pulse operations need attention' : 'Pulse operations recovered';
        $message = $problem ? 'One or more protected health checks are degraded. Review Operations health.' : 'Protected health checks returned to healthy.';
        $recipients = User::query()->where('status', 'active')->whereNotNull('email_verified_at')->where(function ($query) {
            $query->whereHas('roles', fn ($roles) => $roles->where('is_super_admin', true))
                ->orWhereHas('roles.permissions', fn ($permissions) => $permissions->where('name', Permission::ManageSystem->value));
        })->limit(100)->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new OperationalAlert('health.overall', $title, $message, $status->value));
        }

        $this->info('Health transition notification sent to '.$recipients->count().' operator(s).');

        return self::SUCCESS;
    }
}
