# Operations architecture

## Boundary and authorization

M10 adds a cohesive Operations vertical under `Domain/Operations`, `Services/Operations`, Operations controllers, commands, models and private presentation. Every administrative Operations route is inside the existing authenticated, active-account, verified-email and privileged-MFA boundary and additionally requires `system.manage`. Mutations retain Laravel CSRF protection. `/up` is deliberately separate: it returns only `OK`, status 200 and a plain-text content type.

This milestone does not include installation, deployment automation, backups, restore, upgrades, rollback, release SQL or release packaging. Those remain M11.

## Health and observability

`HealthResult` and `HealthStatus` provide stable keys, labels, `healthy`, `degraded`, `unhealthy` or `unknown` states, sanitized summaries, timestamps and bounded safe metadata. `HealthManager` aggregates first-party checks deterministically: any unhealthy result makes the aggregate unhealthy; otherwise degraded or unknown makes it degraded. Checks cover database connection, a namespaced temporary cache round trip, an isolated private-storage write/read/delete probe, database queue state, scheduler state, payment/webhook/reconciliation backlog, and runtime configuration. Expected dependency failures become sanitized results; no provider network request runs while rendering the dashboard.

The protected overview exposes cheap counts for queue failures/backlog and payment operational state. Provider configuration readiness is local only. Gateway sandbox/live certification, real MySQL concurrency and browser/assistive-technology qualification remain environment gates rather than runtime failures.

## Scheduler and queue

`operations:heartbeat` records started/completed times, status and duration every minute. A heartbeat is current for 180 seconds, late until 600 seconds and stale thereafter; both thresholds are configurable. Scheduled content publishing and commerce reservation/order expiry retain bounded batches and overlap locks. `operations:reconcile-payments --batch=25` selects only enabled, fully configured gateways and runs every 15 minutes behind an overlap lock. `operations:prune --batch=100` runs daily. Command output is counts only.

Database queue remains the portable default. The protected queue screen reports driver, pending count, oldest age, failed count and last failure. Failed rows are paginated; payloads and traces are never rendered. One-job retry/forget actions require the full Operations boundary and are audited. Queued financial mutations must remain idempotent and must be dispatched after commit where applicable. Workers should use graceful restarts and a timeout below the configured `retry_after`; process supervision is an operator concern and not provisioned here.

## Alerts and audit

Laravel database notifications provide the first-party in-app channel. `operations:monitor` evaluates aggregate state every five minutes, stores only the last aggregate state in the namespaced cache, and notifies only on state transitions. Recipients are capped at 100 and derived from active, verified super administrators or users holding `system.manage`; ordinary or disabled users are excluded. Recovery creates a distinct notification. The UI provides a 25-row paginated list and owner-scoped mark-one-read mutation.

`AuditLog` remains the existing evidence store. Model update and delete operations now fail, and Operations exposes no mutation routes. The viewer permits allow-listed equality filters plus a maximum 93-day date window and fixed newest-first pagination. Context is redacted again for presentation. Retry, forget, export request and export download record metadata-only audit events.

## Logging and correlation

Portable logging defaults to Laravel daily rotation with 14-day retention. A centralized Monolog processor recursively redacts case-insensitive sensitive nested keys and inline authorization/token/password/secret patterns. A second redaction pass protects the viewer. Every HTTP request receives a random UUID correlation ID, safe route/method/actor context and an `X-Correlation-ID` response header; request bodies, query strings, cookies, full URLs and headers are not added.

The log viewer recognizes only regular, non-symlink `laravel*.log` files whose canonical paths remain under `storage/logs`. It accepts no paths, reads at most 256 KiB from the tail, returns at most 500 lines, and limits literal application-level search to 100 characters. Blade escapes every line.

## Exports and retention

The closed export registry supports Orders, Payments, Products and Audit metadata. It uses fixed columns and queries, lazy 200-row iteration, a configurable 5,000-row default/50,000 hard ceiling, UTF-8 BOM and `fputcsv`. Cells beginning with `=`, `+`, `-` or `@` receive a leading apostrophe to neutralize formulas. Files use unguessable names under the private local disk, expire after 24 hours by default, remain owner-authorized at download, and never accept a client path. Pruning deletes only expired rows/files with the controlled prefix in bounded batches.

Form-submission export is intentionally not enabled because its PII policy needs a separately designed authority. Large asynchronous exports remain future work if the bounded synchronous ceiling proves insufficient.

## Query and index review

M10 retained pagination across completed admin lists and added bounds to all Operations screens and commands. Evidence-based indexes were added for audit time/action filtering and verified/unprocessed webhook operational queries; jobs already index queue and failed jobs already index connection/queue/failure time. Export queries select fixed columns and use `lazyById`, and health uses aggregate counts without payload loading. No speculative general-purpose indexes or arbitrary sort fields were added.

## M11 handoff

M11 can consume `/up`, protected readiness, scheduler heartbeat and the documented worker assumptions while focusing exclusively on secure bootstrap/installer work, deployment documentation, backup/restore, upgrades/rollback, MySQL qualification, release SQL, clean-import validation, final E2E/accessibility/security qualification and release artifacts.
