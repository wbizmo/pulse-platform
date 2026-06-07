<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    public function edit(Page $page): View
    {
        return view('admin.builder.edit', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $request->validate([
            'builder_data' => ['nullable', 'string'],
        ]);

        $decoded = null;

        if ($request->filled('builder_data')) {
            $decoded = json_decode($request->builder_data, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors([
                        'builder_data' => 'Builder JSON is invalid. Please check your formatting.',
                    ])
                    ->withInput();
            }
        }

        $page->update([
            'builder_data' => $decoded,
        ]);

        return back()->with('success', 'Page builder content saved successfully.');
    }
}
