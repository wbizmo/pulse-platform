<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PluginController extends Controller
{
    public function index(): View
    {
        return view('admin.plugins.index', [
            'plugins' => Plugin::orderByDesc('is_active')
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->groupBy('category'),
            'activePluginsCount' => Plugin::where('is_active', true)->count(),
            'pluginsCount' => Plugin::count(),
        ]);
    }

    public function toggle(Plugin $plugin): RedirectResponse
    {
        $plugin->update([
            'is_active' => ! $plugin->is_active,
        ]);

        $message = $plugin->is_active
            ? "{$plugin->name} has been activated."
            : "{$plugin->name} has been deactivated.";

        return back()->with('success', $message);
    }
}
