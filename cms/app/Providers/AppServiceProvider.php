<?php

namespace App\Providers;

use App\Pulse\Plugins\PluginManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Pulse/Plugins/helpers.php');

        $this->app->singleton(PluginManager::class, function () {
            return new PluginManager;
        });
    }

    public function boot(): void
    {
        //
    }
}
