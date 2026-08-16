# Installation

Pulse supports PHP 8.3+ with MySQL 8.0. The document root **must** be `cms/public`; never expose the repository root, `.env`, storage, or vendor directories.

1. Create an empty MySQL database and a least-privilege application account.
2. Copy `cms/.env.example` to `cms/.env`; set the production URL, MySQL connection, mail, cache, session and queue configuration. Set `APP_ENV=production` and `APP_DEBUG=false`.
3. From `cms/`, run `composer install --no-dev --prefer-dist --optimize-autoloader`, `php artisan key:generate`, `npm ci`, and `npm run build`.
4. Grant the web/worker account write access only to `storage/` and `bootstrap/cache/`. Do not use world-writable permissions.
5. Run `php artisan pulse:preflight`, then `php artisan pulse:install`. The installer prompts invisibly for a strong administrator password, applies canonical migrations/system seeds, creates exactly one supplied super administrator, and writes a database installation lock. It has no bypass and refuses re-entry.
6. Sign in, complete MFA enrollment, verify Operations readiness, and start the worker and scheduler described in [Deployment](DEPLOYMENT.md).

`php artisan pulse:status` reports the non-secret installation state. A failed run before the completion record can be corrected and repeated; migrations and system seed synchronization are idempotent. Do not run demo seeders on deployed systems.
