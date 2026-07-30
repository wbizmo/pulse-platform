# Current state

Audit date: 2026-07-30. This describes observed code, not intended behavior.

The repository contains a Laravel 13 / PHP 8.3 application in `cms/`, with Blade-rendered admin/public interfaces and directly served CSS/JavaScript. Eloquent models and migrations exist for users, settings, pages, media, blog taxonomy/posts, menus, themes/settings, and plugins/settings. Controllers and routes provide basic CRUD, builder, theme, plugin, SEO and settings screens. Plugin behavior is currently a small manager/helper rather than a complete isolated runtime.

Authentication is a custom admin login/logout flow. Login regenerates sessions, but currently lacks dedicated request objects, throttling, disabled-account enforcement, password recovery/verification/session management and MFA. Users contain a single string `role`; no role, permission, pivot, policy, user-management or authorization-matrix implementation exists. All authenticated users can currently reach every admin route.

The builder stores page structure and provides existing editing/rendering assets, but its schema lifecycle, nested/reusable structures, concurrency controls and comprehensive tests are incomplete. Themes are database records/settings rather than the three fully packaged first-party themes. Commerce, gateway adapters, webhook ledger, audit logs, notifications, secure installer, protected log viewer, and release SQL are absent.

Only Laravel example tests exist. Dependencies are not installed in this checkout and no `.env` exists, so runtime verification first requires dependency/environment setup. The Git branch is `work`; configured identity exists; no remote is configured.
