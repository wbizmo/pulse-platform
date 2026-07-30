# Current state

Audit date: 2026-07-30. This describes observed code, not intended behavior.

The repository contains a Laravel 13 / PHP 8.3 application in `cms/`, with Blade-rendered admin/public interfaces and directly served CSS/JavaScript. Eloquent models and migrations exist for users, settings, pages, media, blog taxonomy/posts, menus, themes/settings, and plugins/settings. Controllers and routes provide basic CRUD, builder, theme, plugin, SEO and settings screens. Plugin behavior is currently a small manager/helper rather than a complete isolated runtime.

Authentication is a custom admin login/logout flow with a dedicated login request, throttling, session regeneration, and disabled-account enforcement. Normalized roles, permissions, pivots, canonical permission enum, system-role migration, Gate integration, per-route enforcement, permission-aware navigation, and initial authorization matrix tests are implemented. The legacy `users.role` column remains temporarily for compatibility. User/role administration, safe delegation, last-super-administrator protection, password recovery/verification/session management, and MFA are not yet implemented.

The builder stores page structure and provides existing editing/rendering assets, but its schema lifecycle, nested/reusable structures, concurrency controls and comprehensive tests are incomplete. Themes are database records/settings rather than the three fully packaged first-party themes. Commerce, gateway adapters, webhook ledger, audit logs, notifications, secure installer, protected log viewer, and release SQL are absent.

Only Laravel example tests exist. Dependencies are not installed in this checkout and no `.env` exists, so runtime verification first requires dependency/environment setup. The Git branch is `work`; configured identity exists; no remote is configured.
