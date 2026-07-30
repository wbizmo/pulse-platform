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
                <a href="{{ route('admin.mfa.show') }}" class="{{ request()->routeIs('admin.mfa.*') ? 'active' : '' }}"><span class="material-symbols-rounded">security</span>Security</a>
                @can('dashboard.view')

                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">dashboard</span>
                    Dashboard
                </a>

                @endcan

                @can('users.manage')
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span class="material-symbols-rounded">group</span>Users</a>
                @endcan
                @can('roles.manage')
                <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><span class="material-symbols-rounded">admin_panel_settings</span>Roles</a>
                @endcan

                @can('media.manage')


                <a href="{{ route('admin.media') }}" class="{{ request()->routeIs('admin.media*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">perm_media</span>
                    Media
                </a>


                @endcan

                @can('pages.manage')


                <a href="{{ route('admin.pages') }}" class="{{ request()->routeIs('admin.pages*') && ! request()->routeIs('admin.pages.builder*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">article</span>
                    Pages
                </a>


                @endcan

                @can('pages.manage')


                <a href="{{ route('admin.pages') }}" class="{{ request()->routeIs('admin.pages.builder*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">view_quilt</span>
                    Page Builder
                </a>


                @endcan

                @can('posts.manage')


                <a href="{{ route('admin.posts') }}" class="{{ request()->routeIs('admin.posts*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">edit_note</span>
                    Posts
                </a>


                @endcan

                @can('taxonomy.manage')


                <a href="{{ route('admin.categories') }}" class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">category</span>
                    Categories
                </a>


                @endcan

                @can('taxonomy.manage')


                <a href="{{ route('admin.tags') }}" class="{{ request()->routeIs('admin.tags*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">sell</span>
                    Tags
                </a>


                @endcan

                @can('menus.manage')


                <a href="{{ route('admin.menus') }}" class="{{ request()->routeIs('admin.menus*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">menu_open</span>
                    Menus
                </a>


                @endcan

                @can('plugins.manage')


                <a href="{{ route('admin.plugins') }}" class="{{ request()->routeIs('admin.plugins*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">extension</span>
                    Plugins
                </a>


                @endcan

                @can('themes.manage')


                <a href="{{ route('admin.themes') }}" class="{{ request()->routeIs('admin.themes*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">palette</span>
                    Themes
                </a>


                @endcan

                @can('seo.manage')


                <a href="{{ route('admin.seo') }}" class="{{ request()->routeIs('admin.seo*') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">travel_explore</span>
                    SEO
                </a>


                @endcan

                @can('settings.manage')


                <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <span class="material-symbols-rounded">tune</span>
                    Settings
                </a>


                @endcan

                <a href="#">
                    <span class="material-symbols-rounded">monitor_heart</span>
                    Site Health
                </a>

                @can('system.manage')


                <form method="POST" action="{{ route('admin.system.clear-cache') }}" class="pulse-sidebar-cache-form">
                    @csrf

                    <button type="submit">
                        <span class="material-symbols-rounded">cleaning_services</span>
                        Clear Cache
                    </button>
                </form>


                @endcan
            </nav>

            <div class="pulse-sidebar-footer">
                <span class="material-symbols-rounded">verified</span>
                <div>
                    <strong>Pulse CMS</strong>
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
                <a class="pulse-logout" href="{{ route('admin.profile.edit') }}">
                    <span class="material-symbols-rounded">account_circle</span>
                    Profile
                </a>
            </header>

            <section class="pulse-content">
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
