<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Plugins\ChangePluginState;
use App\Domain\Plugins\PluginManifestRegistry;
use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PluginController extends Controller
{
    public function index(PluginManifestRegistry $registry): View
    {
        $rows = Plugin::query()->whereIn('slug', array_keys($registry->all()))->get()->keyBy('slug');

        return view('admin.plugins.index', [
            'manifests' => $registry->all(), 'plugins' => $rows,
            'activePluginsCount' => $rows->where('is_active', true)->count(), 'pluginsCount' => $rows->count(),
        ]);
    }

    public function activate(Plugin $plugin, ChangePluginState $action): RedirectResponse
    {
        $action->activate($plugin, request()->user());

        return back()->with('success', "{$plugin->name} has been activated.");
    }

    public function deactivate(Plugin $plugin, ChangePluginState $action): RedirectResponse
    {
        $action->deactivate($plugin, request()->user());

        return back()->with('success', "{$plugin->name} has been deactivated.");
    }
}
