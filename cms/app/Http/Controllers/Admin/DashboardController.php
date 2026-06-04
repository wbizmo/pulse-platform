<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Plugin;
use App\Models\SiteHealthLog;
use App\Models\Theme;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'activeTheme' => Theme::where('is_active', true)->first(),
            'pluginsCount' => Plugin::count(),
            'activePluginsCount' => Plugin::where('is_active', true)->count(),
            'pagesCount' => Page::count(),
            'latestHealthLogs' => SiteHealthLog::latest()->take(5)->get(),
        ]);
    }
}
