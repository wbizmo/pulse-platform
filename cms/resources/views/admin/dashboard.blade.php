@extends('admin.layouts.app', [
    'title' => 'Pulse CMS Dashboard',
    'heading' => 'Dashboard',
    'subheading' => 'A clean overview of your CMS, site tools, and system status.'
])

@section('content')
    <section class="pulse-grid pulse-grid-4">
        <article class="pulse-card">
            <div class="pulse-card-icon">
                <span class="material-symbols-rounded">palette</span>
            </div>
            <p>Active Theme</p>
            <h2>Pulse Business</h2>
            <small>Default business theme</small>
        </article>

        <article class="pulse-card">
            <div class="pulse-card-icon">
                <span class="material-symbols-rounded">extension</span>
            </div>
            <p>Plugins</p>
            <h2>12 active</h2>
            <small>Bundled with the CMS</small>
        </article>

        <article class="pulse-card">
            <div class="pulse-card-icon">
                <span class="material-symbols-rounded">article</span>
            </div>
            <p>Pages</p>
            <h2>7 pages</h2>
            <small>Default content installed</small>
        </article>

        <article class="pulse-card">
            <div class="pulse-card-icon">
                <span class="material-symbols-rounded">monitor_heart</span>
            </div>
            <p>System</p>
            <h2>Healthy</h2>
            <small>No critical issues</small>
        </article>
    </section>

    <section class="pulse-dashboard-split">
        <div class="pulse-panel">
            <div class="pulse-panel-head">
                <div>
                    <h3>Quick actions</h3>
                    <p>Common tools for building and maintaining the site.</p>
                </div>
            </div>

            <div class="pulse-action-grid">
                <button class="pulse-action-card">
                    <span class="material-symbols-rounded">add_circle</span>
                    <strong>Create page</strong>
                    <small>Add a new page to the site.</small>
                </button>

                <button class="pulse-action-card">
                    <span class="material-symbols-rounded">view_quilt</span>
                    <strong>Open builder</strong>
                    <small>Design content blocks visually.</small>
                </button>

                <button class="pulse-action-card">
                    <span class="material-symbols-rounded">extension</span>
                    <strong>Manage plugins</strong>
                    <small>Enable or disable bundled modules.</small>
                </button>

                <button class="pulse-action-card">
                    <span class="material-symbols-rounded">health_and_safety</span>
                    <strong>Run site health</strong>
                    <small>Check permissions, cache, mail, and storage.</small>
                </button>
            </div>
        </div>

        <div class="pulse-panel">
            <div class="pulse-panel-head">
                <div>
                    <h3>Build roadmap</h3>
                    <p>What this v1 foundation will grow into.</p>
                </div>
            </div>

            <div class="pulse-roadmap">
                <div>
                    <span></span>
                    <p>Installer wizard for cPanel/shared hosting</p>
                </div>
                <div>
                    <span></span>
                    <p>Theme and plugin activation system</p>
                </div>
                <div>
                    <span></span>
                    <p>Settings, branding, contact, and SEO controls</p>
                </div>
                <div>
                    <span></span>
                    <p>Static HTML/CSS site export plugin</p>
                </div>
            </div>
        </div>
    </section>
@endsection
