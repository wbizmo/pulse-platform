# Troubleshooting

Run `php artisan pulse:preflight` and correct every failure rather than bypassing it. Verify PHP extensions, the application key, MySQL reachability/privileges, and write access for `storage` and `bootstrap/cache`. Never use `chmod -R 777`.

If installation stops before completion, preserve the error, correct configuration, and rerun; canonical migrations/seeds are idempotent. If `pulse:status` says installed, do not delete the lock or rerun—restore or follow the upgrade procedure. For 419 responses check HTTPS URL, proxy trust, session persistence and cookies. For 403 responses confirm active/verified identity, assigned permission and completed MFA. For stale Operations heartbeat confirm the single scheduler cron. For queued mail/notifications inspect protected failed-job/log diagnostics, correct the cause, retry deliberately, and restart workers.

After deployment clear stale caches with `php artisan optimize:clear`, then rebuild production caches. Capture server stderr and `storage/logs` during HTTP diagnosis; do not expose either publicly.
