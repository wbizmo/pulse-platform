<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $checkboxes = [
            'show_email',
            'show_phone',
            'show_address',
            'show_contact_form',
            'enable_preloader',
            'maintenance_mode',
        ];

        foreach ($checkboxes as $checkbox) {
            Setting::setValue($checkbox, $request->has($checkbox) ? '1' : '0');
        }

        foreach ($request->except(array_merge(['_token'], $checkboxes)) as $key => $value) {
            Setting::setValue($key, $value);
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
