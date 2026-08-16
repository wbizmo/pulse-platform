<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function clearCache(Request $request, RecordAudit $audit): RedirectResponse
    {
        Artisan::call('optimize:clear');
        $audit->execute($request->user(), 'operations.cache_cleared', $request->user(), ['scope' => 'application']);

        return back()->with('success', 'Application cache cleared successfully.');
    }
}
