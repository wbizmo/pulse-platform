# Engineering contract

## Delivery

Extend the existing application through runnable vertical slices. Repair broken, insecure, incomplete, duplicated, obsolete, inaccessible, poorly separated, or untested behavior. Remove abandoned helpers/routes/views, duplicate assets, unused imports/dependencies, backups and debugging output. Any accepted compromise belongs in the technical-debt register with reason, risk, module, remediation, milestone and release impact.

## Code boundaries

Use a Laravel modular monolith. Controllers translate HTTP; Form Requests validate; policies/gates and middleware authorize; actions coordinate use cases; domain services/value objects/enums enforce invariants; repositories/Eloquent persist; contracts and adapters isolate providers; events/listeners/jobs/notifications handle asynchronous effects; views only present escaped data. Dependencies must be explicit.

Avoid fat controllers/models, god services, route/Blade business logic, repeated authorization, duplicate queries, global mutable state, arbitrary helpers, premature interfaces, dead code, and direct environment reads outside config. Paginate collections, eager-load intentionally, index critical access paths, bound/chunk exports, queue slow and retryable work, invalidate caches explicitly, and make jobs idempotent.

Money uses integer minor units plus explicit ISO currency—never floats. Historical order line/discount/tax totals are immutable snapshots. Stock and payment transitions use transactions, locks, unique constraints, idempotency keys and explicit state machines.

## Interface contract

Pulse has its own tokenized design system: spacing/type scales, semantic colors, surfaces, borders, radii, shadows, focus, breakpoints, motion/reduced-motion, component states and icon rules. Reusable components cover buttons, fields, selects, checkboxes, switches, tabs, breadcrumbs, dropdowns, tables, filters, pagination, badges, dialogs/drawers, skeletons, empty/confirmation states, uploads, and toasts. Toasts safely escape content and support four semantic levels, actions, dismissal/persistence, queueing, accessible announcements and mobile/reduced-motion behavior. Native alert/confirm is prohibited.

## Completion gate

A slice includes validation, backend authorization, accessible UI authorization, migrations/indexes, tests (including direct-access denial), docs, operational concerns, diff review, recovery update and focused commit. Compilation alone is not functional evidence. Run affected workflows at desktop/tablet/mobile when UI changes.

## Git and documentation

Retain configured identity, one implementation branch, focused non-empty commits, normal pushes, and no AI/co-author attribution or history rewriting. Important decisions get ADRs. Documentation distinguishes current from planned behavior and covers installation, database, auth/RBAC, builder, plugins/themes, commerce/payments/webhooks, deployment, queue/scheduler, backup/restore, upgrades/rollback, testing, security and troubleshooting.
