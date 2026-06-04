@extends('admin.layouts.app', [
    'title' => 'Pulse Dashboard',
    'heading' => 'Dashboard',
    'subheading' => 'A quick overview of your Pulse CMS installation.'
])

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-white border border-slate-200 rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Active Theme</p>
                <span class="material-symbols-rounded text-slate-400">palette</span>
            </div>
            <h2 class="mt-4 text-2xl font-semibold">{{ $activeTheme?->name ?? 'None' }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $activeTheme?->version ?? 'No version' }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Plugins</p>
                <span class="material-symbols-rounded text-slate-400">extension</span>
            </div>
            <h2 class="mt-4 text-2xl font-semibold">{{ $activePluginsCount }} / {{ $pluginsCount }}</h2>
            <p class="mt-1 text-sm text-slate-500">Active plugins</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">Pages</p>
                <span class="material-symbols-rounded text-slate-400">article</span>
            </div>
            <h2 class="mt-4 text-2xl font-semibold">{{ $pagesCount }}</h2>
            <p class="mt-1 text-sm text-slate-500">Created pages</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-500">System</p>
                <span class="material-symbols-rounded text-slate-400">monitor_heart</span>
            </div>
            <h2 class="mt-4 text-2xl font-semibold">Healthy</h2>
            <p class="mt-1 text-sm text-slate-500">No critical issues</p>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white border border-slate-200 rounded-3xl p-6">
            <h3 class="text-lg font-semibold">Quick Actions</h3>
            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <button class="text-left rounded-2xl border border-slate-200 p-5 hover:bg-slate-50">
                    <span class="material-symbols-rounded">add_circle</span>
                    <div class="mt-3 font-medium">Create Page</div>
                    <div class="text-sm text-slate-500">Start a new page.</div>
                </button>

                <button class="text-left rounded-2xl border border-slate-200 p-5 hover:bg-slate-50">
                    <span class="material-symbols-rounded">extension</span>
                    <div class="mt-3 font-medium">Manage Plugins</div>
                    <div class="text-sm text-slate-500">Activate site features.</div>
                </button>

                <button class="text-left rounded-2xl border border-slate-200 p-5 hover:bg-slate-50">
                    <span class="material-symbols-rounded">health_and_safety</span>
                    <div class="mt-3 font-medium">Site Health</div>
                    <div class="text-sm text-slate-500">Check system status.</div>
                </button>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6">
            <h3 class="text-lg font-semibold">Health Logs</h3>

            <div class="mt-5 space-y-4">
                @forelse ($latestHealthLogs as $log)
                    <div class="border border-slate-100 rounded-2xl p-4">
                        <div class="font-medium">{{ $log->check_name }}</div>
                        <div class="text-sm text-slate-500">{{ $log->message }}</div>
                    </div>
                @empty
                    <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                        No health checks have been recorded yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
