<?php

namespace App\Providers;

use App\Domain\Access\Permission;
use App\Domain\Plugins\PluginManifestRegistry;
use App\Models\User;
use App\Payments\PaymentGatewayRegistry;
use App\Pulse\Plugins\PluginRuntime;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Pulse/Plugins/helpers.php');

        $this->app->singleton(PluginManifestRegistry::class, fn () => new PluginManifestRegistry);
        $this->app->singleton(PluginRuntime::class);
        $this->app->singleton(PaymentGatewayRegistry::class);
    }

    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->isSuperAdministrator() ? true : null;
        });

        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user): bool => $user->hasPermission($permission->value));
        }
    }
}
