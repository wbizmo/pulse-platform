# Page and post lifecycle

## State and time contract

Pages and posts use `draft`, `scheduled`, `published`, and `archived`. Lifecycle input is validated centrally by content Form Requests and persisted by `SaveContent`. Draft and archived records have no publication timestamp. Scheduled records require a future timestamp. Published records receive the current application time when no timestamp is supplied and cannot carry a future timestamp. The application timezone is Laravel's configured `app.timezone`; production database timestamps are UTC and Laravel converts them at the boundary.

Publishing, scheduling, rescheduling, cancelling a schedule, unpublishing, archiving, and restoring an archive all use the same invariant checks. Deletion remains hard deletion because the current schema has no soft deletes. Mutations write bounded audit context without content bodies.

## Visibility, discovery, and slugs

`publiclyVisible` is the only publication eligibility scope. It requires published state, a publication time, and a time no later than now. Public page, home, blog, post, and sitemap queries use it. Pages and posts have separate unique slug namespaces. Input is normalized to ASCII kebab case and compared case-insensitively; reserved system and catch-all page paths are rejected, while database unique constraints provide the final concurrency boundary.

Administration previews use expiring Laravel signatures, existing page/post permission checks, signed identifiers, private/no-store caching, and `X-Robots-Tag: noindex, nofollow`. Preview never changes publication state.

## Scheduling and concurrency

Run Laravel's scheduler every minute: `* * * * * php artisan schedule:run`. `content:publish-scheduled` reads a bounded batch and conditionally updates status and time, so overlapping workers can transition a row only once. `withoutOverlapping` provides an additional scheduler guard. Success is audited and invalidates the sitemap cache key; failures are logged. `--batch` defaults to 100 and is capped at 1000.

Editor forms submit `lock_version`; updates condition on and atomically increment it. Stale updates fail validation and preserve the newer record. Post taxonomy synchronization and page special assignments are transactional.

## Current boundaries

Pages have no parent field, so hierarchical URLs and cycle handling do not apply. Featured media remains a validated URL rather than a media foreign key; conversion belongs to the following M3 media slice. Builder JSON remains the M4 versioned-schema source. Full revision storage/restoration aligns with M4 and is not delivered here.
