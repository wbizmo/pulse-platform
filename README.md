# Pulse CMS

Pulse is a security-first Laravel publishing and commerce platform by [Williams (wbizmo)](https://github.com/wbizmo). The repository implements the M1–M10 product domains and the M11 secure installation/release foundation. Release qualification is evidence-driven: see the checklist for the remaining environment and owner gates rather than interpreting implemented adapters as live certification.

## Platform

Pulse runs on PHP 8.3+, Laravel 13, Blade, Tailwind CSS 4 and Vite 8. MySQL 8.0 is the production database target; SQLite is used for fast isolated tests but is not production or concurrency evidence. PostgreSQL support is not claimed. Built assets are committed to a deployable release and Node.js is not needed at runtime.

## Capabilities

### Identity and security

Pulse provides active/disabled account lifecycle controls, verified email and non-enumerating recovery, owned database-session visibility and revocation, TOTP MFA with single-use recovery codes, granular deny-by-default RBAC, bounded role delegation and append-only security audits. Every administration route enforces backend authority; privileged capabilities require verified identity and MFA.

### Publishing

Pages and posts have draft/scheduled/published lifecycle, optimistic versioning, private previews, managed taxonomy, managed raster media, deterministic menus, centralized SEO and structured data, and first-party Forms with immutable submission snapshots and abuse controls.

Builder V4 stores a versioned, server-validated nested document from a closed block catalogue. It supports nesting, keyboard reorder, responsive preview, persisted templates, page/version-scoped recovery, dirty-navigation warnings and optimistic conflict handling. Stored arbitrary HTML is not executed.

The Theme Platform ships **Pulse Studio**, **Pulse Corporate**, and **Pulse Commerce**, with typed settings, preview, atomic activation and rollback. The first-party Plugin Runtime uses code-owned manifests, dependency/compatibility validation, typed settings, canonical permissions, explicit safe contributions and failure isolation—never uploaded executable archives.

### Commerce and payments

The catalogue includes categories, products, variants, globally unique SKUs, managed media and integer-minor-unit money. Inventory uses an append-only ledger, locked materialized balances and expiring reservations. Server-side guest carts feed idempotent checkout, immutable order snapshots, coupons, tax/shipping rules and order history without trusting browser prices.

A gateway-neutral payment domain integrates Stripe, PayPal, Flutterwave and Paystack adapters. It verifies webhook signatures and replay identity, owns amount/currency decisions, fulfils atomically, and supports idempotent refunds, disputes and reconciliation. Deterministic contract tests do not equal sandbox/live provider certification; credential-dependent certification remains a release-owner gate.

### Operations

Protected Operations surfaces expose typed readiness, database queue and scheduler heartbeat, redacted bounded logs, transition-deduplicated notifications, append-only audits, formula-safe private exports, payment visibility and pruning. `/up` reveals only minimal liveness.

## Secure installation

The web root **must be `cms/public`**. There is no public installer, `.env` editor, default administrator or force bypass.

```bash
cd cms
cp .env.example .env                 # configure production MySQL, URL, mail and drivers
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan key:generate
npm ci
npm run build
php artisan pulse:preflight
php artisan pulse:install            # hidden password prompts; then enroll MFA
php artisan pulse:status
```

The normal `DatabaseSeeder` synchronizes only canonical roles, permissions, themes and plugins. Demo accounts are isolated behind an explicit non-production seeder and fail closed in production. Migrations remain canonical. A MySQL release SQL artifact may be published only after the clean-database generation/import and forbidden-data checks in the release checklist; none is represented as verified merely from SQLite.

See [Installation](docs/release/INSTALLATION.md) and [Deployment](docs/release/DEPLOYMENT.md) for permissions, HTTPS, caches, mail, storage and provider configuration.

## Production processes

Run the database queue baseline under a supervisor (or deliberately configure monitored Redis):

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600
php artisan queue:restart
```

Run one scheduler cron, never one cron per task:

```cron
* * * * * cd /path/to/cms && php artisan schedule:run >> /dev/null 2>&1
```

This drives scheduler heartbeat, publishing, reservation/order expiry, monitoring, reconciliation and pruning.

## Verification

From `cms/`:

```bash
composer validate --strict
vendor/bin/pint --test
php artisan test
php artisan route:list
php artisan schedule:list
npm run build
composer audit --locked
npm audit
```

Release additionally requires genuine MySQL fresh-install and parallel race evidence, release-SQL clean import/schema comparison, production no-dev/cache/HTTP smoke, backup restore and M10 upgrade rehearsal, security review, and browser/keyboard/accessibility automation. Human assistive-technology testing and live payment certification are explicitly manual owner gates unless actually recorded. See [Release checklist](docs/release/RELEASE_CHECKLIST.md).

## Backup, upgrades and recovery

Back up MySQL plus persistent public/private application files and release metadata into encrypted controlled storage; keep `.env` and secret-manager material outside ordinary backups. Always drill a disposable restore. Database restore—not universally `migrate:rollback`—is authoritative when reverting incompatible data changes. Follow [Backup/restore](docs/release/BACKUP_RESTORE.md), [Upgrade/rollback](docs/release/UPGRADE_ROLLBACK.md), and [Troubleshooting](docs/release/TROUBLESHOOTING.md).

## Repository map

- `cms/app/Actions`, `Domain`, `Payments` — application/domain/integration boundaries
- `cms/app/Http` — validated and authorized HTTP delivery
- `cms/app/Console` — installer, scheduler and operational commands
- `cms/app/Models`, `database/migrations`, `database/seeders` — persistence and canonical schema/bootstrap
- `cms/resources/views`, `resources/css`, `resources/js` — accessible server-rendered presentation
- `cms/tests` — unit and feature security/regression coverage
- `docs/architecture`, `docs/security`, `docs/testing` — governing technical contracts
- `docs/release` — installation, deployment and recovery runbooks

Architecture detail starts with [Current state](docs/architecture/CURRENT_STATE.md), [Engineering contract](docs/engineering/ENGINEERING_CONTRACT.md), and [Security requirements](docs/security/SECURITY_REQUIREMENTS.md).

## License

Pulse CMS is released under the [MIT License](LICENSE). Copyright Williams; project maintained at [wbizmo/pulse-platform](https://github.com/wbizmo/pulse-platform).
