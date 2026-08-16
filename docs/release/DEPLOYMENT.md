# Production deployment

Serve only `cms/public` over HTTPS. Terminate TLS at a trusted proxy that replaces forwarded headers. Deploy immutable application releases; persist `.env`, `storage/app/public`, required private files in `storage/app/private`, and MySQL separately. Built assets in `public/build` require no Node.js at runtime.

After dependency installation and asset build, run `php artisan migrate --force`, `php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder --force`, then `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, and `php artisan event:cache`. Restart workers with `php artisan queue:restart`. Roll back the release if any cache command fails.

Use `php artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600` under a process supervisor. The database queue is the baseline; Redis may be selected only after configuring and monitoring it. Run exactly one cron entry: `* * * * * cd /path/to/cms && php artisan schedule:run >> /dev/null 2>&1`. The schedule owns heartbeat, scheduled publishing, reservation/order expiry, payment reconciliation, monitoring, and pruning. Alert on stale heartbeat, failed jobs, and Operations warnings.

Configure a production mail transport and the public/private storage disks. Configure Stripe, PayPal, Flutterwave, or Paystack only through their encrypted administration workflow, use HTTPS callback/webhook URLs, and register the documented application endpoints shown by `php artisan route:list`. Never put credentials in documentation or source control. Signature verification, replay protection, amount/currency checks, refunds, disputes, and reconciliation remain server-owned. Sandbox/live provider certification requires owner credentials and is a release-owner gate.

`/up` is deliberately minimal. Detailed health, logs, queue controls, notifications, audits, and exports require authenticated RBAC plus MFA in Operations.
