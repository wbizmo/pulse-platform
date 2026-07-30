<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulse CMS - Admin Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,300,0,0&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="pulse-auth-body">
    <main class="pulse-auth-shell">
        <section class="pulse-auth-card">
            <div class="pulse-auth-brand">
                <div class="pulse-logo-mark">
                    <span class="material-symbols-rounded">bolt</span>
                </div>

                <div>
                    <h1>Pulse CMS</h1>
                    <p>Admin workspace</p>
                </div>
            </div>

            <div class="pulse-auth-copy">
                <h2>Welcome back</h2>
                <p>
                    Sign in to manage your pages, themes, plugins,
                    settings, SEO, site health, and content.
                </p>
            </div>

            @if ($errors->any())
                <div class="pulse-alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="pulse-form">
                @csrf

                <label>
                    <span>Email address</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="admin@pulse.test"
                    >
                </label>

                <label>
                    <span>Password</span>
                    <input
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                    >
                </label>

                <div class="pulse-form-row">
                    <label class="pulse-switch">
                        <input type="checkbox" name="remember">

                        <span class="pulse-switch-track">
                            <span class="pulse-switch-thumb"></span>
                        </span>

                        <span class="pulse-switch-text">
                            Remember me
                        </span>
                    </label>
                </div>

                <button type="submit" class="pulse-btn pulse-btn-dark">
                    <span>Sign in</span>

                    <span class="material-symbols-rounded">
                        arrow_forward
                    </span>
                </button>
                <a href="{{ route('admin.password.request') }}">Forgot your password?</a>
            </form>

            <p class="pulse-auth-foot">
                Pulse CMS Administration
            </p>
        </section>

        <section class="pulse-auth-panel">
            <div class="pulse-panel-glow"></div>

            <div class="pulse-panel-content">
                <span class="material-symbols-rounded">
                    dashboard_customize
                </span>

                <h2>Build. Manage. Customize.</h2>

                <p>
                    A Laravel-powered CMS built for business websites,
                    ecommerce stores, schools, blogs, portfolios,
                    landing pages, and more.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
