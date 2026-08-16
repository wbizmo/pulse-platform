<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Plugins\PluginHookEvent;
use App\Http\Controllers\Controller;
use App\Pulse\Plugins\PluginRuntime;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PluginRuntime $runtime): View
    {
        $runtime->dispatch(new PluginHookEvent('dashboard.loaded'));

        return view('admin.dashboard', ['pluginWidgets' => $runtime->widgets()]);
    }
}
