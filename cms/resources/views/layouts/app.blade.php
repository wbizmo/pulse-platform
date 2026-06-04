<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Pulse Admin' }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,300,0,0&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen flex">
        <aside class="w-72 bg-white border-r border-slate-200 hidden lg:flex lg:flex-col">
            <div class="h-20 px-6 flex items-center border-b border-slate-100">
                <div>
                    <div class="text-lg font-semibold tracking-tight">Pulse CMS</div>
                    <div class="text-xs text-slate-500">Control center</div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-5 space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-slate-900 text-white">
                    <span class="material-symbols-rounded text-[20px]">dashboard</span>
                    Dashboard
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-100">
                    <span class="material-symbols-rounded text-[20px]">article</span>
                    Pages
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-100">
                    <span class="material-symbols-rounded text-[20px]">extension</span>
                    Plugins
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-100">
                    <span class="material-symbols-rounded text-[20px]">palette</span>
                    Themes
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 hover:bg-slate-100">
                    <span class="material-symbols-rounded text-[20px]">settings</span>
                    Settings
                </a>
            </nav>
        </aside>

        <main class="flex-1">
            <header class="h-20 bg-white border-b border-slate-200 px-6 lg:px-10 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">{{ $heading ?? 'Dashboard' }}</h1>
                    <p class="text-sm text-slate-500">{{ $subheading ?? 'Manage your site, plugins, themes, and system health.' }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm hover:bg-slate-700">
                        Logout
                    </button>
                </form>
            </header>

            <section class="p-6 lg:p-10">
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
