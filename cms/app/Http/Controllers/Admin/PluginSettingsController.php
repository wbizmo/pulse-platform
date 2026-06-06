<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\PluginSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PluginSettingsController extends Controller
{
    public function index(Plugin $plugin): View
    {
        $settings = $plugin->settings()
            ->pluck('value', 'key');

        $view = match ($plugin->category) {
            'mail' => 'admin.plugins.settings.mail',
            'payments' => 'admin.plugins.settings.payments',
            'forms' => 'admin.plugins.settings.forms',
            'analytics' => 'admin.plugins.settings.analytics',
            'security' => 'admin.plugins.settings.security',
            default => 'admin.plugins.settings.index',
        };

        return view($view, [
            'plugin' => $plugin,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, Plugin $plugin): RedirectResponse
    {
        $checkboxes = [
            'enabled',
            'test_mode',
            'auto_reply_enabled',
            'html_email_enabled',
            'save_submissions',
            'spam_protection',
            'force_https',
            'security_headers',
            'cookie_consent',
            'show_on_frontend',
        ];

        foreach ($checkboxes as $checkbox) {
            PluginSetting::updateOrCreate(
                [
                    'plugin_id' => $plugin->id,
                    'key' => $checkbox,
                ],
                [
                    'value' => $request->has($checkbox) ? '1' : '0',
                ]
            );
        }

        foreach ($request->except(array_merge(['_token'], $checkboxes)) as $key => $value) {
            PluginSetting::updateOrCreate(
                [
                    'plugin_id' => $plugin->id,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        return back()->with('success', "{$plugin->name} settings saved successfully.");
    }
}
