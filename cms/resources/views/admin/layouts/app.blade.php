<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Pulse Admin' }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,300,0,0&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="{{ asset('js/admin.js') }}" defer></script>
</head>
<body class="pulse-admin-body">
    <div class="pulse-admin-shell">
        <aside class="pulse-sidebar" id="pulseSidebar">
            <div class="pulse-sidebar-brand">
                <div class="pulse-logo-mark">
                    <span class="material-symbols-rounded">bolt</span>
                </div>

                <div>
                    <strong>Pulse CMS</strong>
                    <small>Control center</small>
                </div>
            </div>

            <nav class="pulse-nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">dashboard</span>
                    Dashboard
                </a>

                <a href="{{ route('admin.pages') }}" class="{{ request()->routeIs('admin.pages*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">article</span>
                    Pages
                </a>

                <a href="{{ route('admin.menus') }}" class="{{ request()->routeIs('admin.menus*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">menu_open</span>
                    Menus
                </a>

                <a href="#">
                    <span class="material-symbols-rounded">view_quilt</span>
                    Page Builder
                </a>

                <a href="{{ route('admin.plugins') }}" class="{{ request()->routeIs('admin.plugins*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">extension</span>
                    Plugins
                </a>

                <a href="{{ route('admin.themes') }}" class="{{ request()->routeIs('admin.themes*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">palette</span>
                    Themes
                </a>

                <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">tune</span>
                    Settings
                </a>

                <a href="#">
                    <span class="material-symbols-rounded">monitor_heart</span>
                    Site Health
                </a>
            </nav>

            <div class="pulse-sidebar-footer">
                <span class="material-symbols-rounded">verified</span>
                <div>
                    <strong>v1 Foundation</strong>
                    <small>Blade-only build</small>
                </div>
            </div>
        </aside>

        <main class="pulse-main">
            <header class="pulse-topbar">
                <button class="pulse-icon-btn" type="button" data-sidebar-toggle>
                    <span class="material-symbols-rounded">menu</span>
                </button>

                <div>
                    <h1>{{ $heading ?? 'Dashboard' }}</h1>
                    <p>{{ $subheading ?? 'Manage your Pulse CMS workspace.' }}</p>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf

                    <button class="pulse-logout" type="submit">
                        <span class="material-symbols-rounded">logout</span>
                        Logout
                    </button>
                </form>
            </header>

            <section class="pulse-content">
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
