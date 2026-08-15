# Test strategy

Use PHPUnit at unit, feature and integration layers. Unit tests cover values, state machines, schema validation and pure domain rules. Feature tests cover HTTP validation, CSRF/session behavior, policy/direct-access denial, CRUD and rendered UI restrictions. Integration tests cover database constraints/locking, adapters, queued jobs, encryption and webhook inbox behavior. Add regression tests for repaired defects.

Maintain an authorization matrix for anonymous, disabled, unverified, each permission combination and super-administrator behavior across routes, UI, jobs, commands, exports/downloads and sensitive actions. Payment contract tests run identical scenarios against every adapter: success/pending/failure, timeout, malformed/rate-limited responses, currency/amount mismatch, idempotency, duplicate/out-of-order webhooks, refunds/disputes and reconciliation. Exercise simultaneous stock purchase/reservation expiry and duplicate checkout.

Frontend interaction/browser coverage validates builder drag/reorder/nesting/recovery/concurrency, dialogs/toasts, uploads, keyboard/focus behavior, safe escaping, reduced motion, and desktop/tablet/mobile layouts. Use real sandbox gateways only when credentials are explicitly supplied; deterministic fakes may test contracts but never masquerade as shipped integrations.

Baseline commands from `cms/`: `composer validate --strict`, `vendor/bin/pint --test`, `php artisan test`, `php artisan route:list`, and `npm run build`. Release checks add clean database migrations/seed, queue and scheduler exercise, application login/workflow smoke tests, security/config review, and clean import/boot against release SQL. Record exact commands and honest outcomes in `NEXT.md`; never infer success from compilation.


The M1 matrix exercises MFA enrollment enforcement, current-session challenge state, permission-driven enforcement changes, password confirmation, encrypted and hidden secrets, one-way single-use recovery codes, plus denial precedence for disabled and unverified identities. Lifecycle regressions retain signed verification tampering, session and profile ownership, and final-super-administrator invariants.


M2 rendered-interface feature tests cover permission-aware and active navigation, mobile drawer landmarks, escaped flash-to-toast rendering, validation summaries, and destructive dialog triggers. Production Vite compilation is a required frontend gate; browser interaction remains a separate, explicitly reported gate.


M2 follow-up coverage renders a representative legacy content administration screen, verifies its custom destructive-dialog trigger, and scans administration views and JavaScript for prohibited native `alert()` or `confirm()` calls.

The incremental direct-component coverage asserts that taxonomy renders the `p-*` component contract without legacy classes, uses unique repeated-form control identifiers, and retains the custom destructive confirmation trigger.

Direct-component coverage also renders page and post resource lists and editors, verifies responsive table markup, labelled settings, publishing controls, and custom deletion triggers, and rejects legacy class output on those screens.

The administration login smoke gate must exercise a real CLI-server request, not only Laravel's in-process HTTP test client. Build Vite assets and clear configuration, route, event, and compiled-view caches first; then run `php artisan serve` with the isolated testing key, SQLite connection, and both `file` and `array` session drivers. Capture server stdout and stderr separately, require HTTP 200 and the `Sign in to Pulse` heading, and require empty stderr and an empty Laravel log. The feature regression additionally verifies that the anonymous response renders before the session and CSRF cookies are emitted.

M2 completion coverage renders media, menus, themes, plugins, settings, SEO, and the existing builder surface; it checks shared toast regions and rejects retired administration bridge classes and native dialogs. Browser interaction remains a distinct outstanding environment gate.

M3 content lifecycle tests cover draft/scheduled/archived public exclusion, sitemap exclusion, effective publication, idempotent scheduler transitions and audits, optimistic-lock conflicts, and direct preview authentication. Form Request coverage exercises invalid lifecycle/time combinations, reserved and colliding slugs, taxonomy identifiers, and route authorization.

M3 taxonomy coverage exercises normalization and case-equivalent duplicates, application and database uniqueness, invalid/duplicate identifiers, foreign-key integrity, assigned-record deletion denial, audit creation, rendered pagination, and draft/scheduled/archived exclusion from public archives.

M3 media coverage exercises direct authorization, real image metadata derivation, opaque traversal-resistant naming, SVG/executable/malformed rejection, cleanup, pagination and custom-dialog markup, forged featured-media IDs, model relations, and application/database deletion protection. SQLite verifies migrations during the suite; MySQL remains a release-environment gate.

M3 menu coverage exercises normalized validation, singleton activation, hostile custom URLs, coherent item types, current page-derived links, publication filtering, dependency-protected deletion, complete atomic reorder, parent scoping, auditing, and protected new-tab rendering. SQLite foreign-key behavior is covered; real MySQL remains a release-environment gate.


M3 SEO coverage exercises setting-key injection, scalar/type/length/control validation, independent SEO authority, unsafe canonical rejection, deterministic robots directives, visibility-safe bounded sitemap content, homepage deduplication, taxonomy archives, escaped metadata, article JSON-LD, pagination canonicals, and global noindex semantics.

M3 Forms coverage exercises active-state behavior, trusted schema validation, unexpected keys, scalar confusion, immutable snapshots, cross-form mutation/reorder, atomic failure, retention, escaping, throttling, deny-by-default Forms authority, and privileged MFA. SQLite covers fresh migration and Forms migration rollback/reapply; MySQL and browser interaction remain separate release gates.
