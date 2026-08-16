# Upgrade and rollback

Before an upgrade, read the changelog/migrations, take and verify a database plus persistent-file backup, drain writes/workers, record the release commit, deploy code and locked dependencies, run `php artisan migrate --force`, synchronize `DatabaseSeeder`, build caches, restart workers, and execute the checklist.

The M10-to-M11 path starts at merged commit `6e14d7e84131f8f94851075c397623bdab1448c1`. M11 adds the installation lock and production-safe seed split; existing users are preserved and an upgraded site must not run `pulse:install`. Operators should insert an installation record through an approved deployment procedure only after confirming the existing super administrator, or retain the prior release while completing migration qualification.

Rollback means redeploying the recorded prior release and, whenever a migration or write semantic is not backward compatible, restoring the authoritative pre-upgrade database and persistent-file backup. `migrate:rollback` is not a universal recovery mechanism and must not be used as a substitute for verified restoration. Keep the site read-only/offline until database, files and deployed code represent one coherent point in time.
