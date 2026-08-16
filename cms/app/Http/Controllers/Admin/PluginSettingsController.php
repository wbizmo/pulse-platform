<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Plugins\SavePluginSettings;
use App\Domain\Plugins\PluginManifestRegistry;
use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PluginSettingsController extends Controller
{
    public function index(Plugin $plugin, PluginManifestRegistry $registry): View
    {
        $manifest = $registry->get($plugin->slug);

        return view('admin.plugins.settings.index', ['plugin' => $plugin, 'manifest' => $manifest, 'settings' => $plugin->settings()->pluck('value', 'key')]);
    }

    public function update(Request $request, Plugin $plugin, SavePluginSettings $action): RedirectResponse
    {
        $action->execute($plugin, $request->user(), $request->except('_token'));

        return back()->with('success', "{$plugin->name} settings saved successfully.");
    }
}
