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

M4 Builder coverage exercises schema/version/known-key validation, UUID uniqueness, hostile HTML and URL rejection, nesting/depth/node bounds, atomic optimistic conflicts, metadata audits, snapshot template authorization/lifecycle, escaping, fail-closed legacy rendering, and direct Builder authorization/MFA. Real browser interaction must additionally cover editor operations, recovery, focus, before-unload behavior and responsive viewports under TD-005; SQLite migration evidence does not replace real MySQL verification.

M5 coverage verifies the canonical registry, compatibility, hostile setting rejection, atomic singleton activation, audit/history/rollback, failure preservation, legacy retirement and all three pre-commerce renderers. Builder/full regressions remain required; browser viewports are TD-005 and MySQL remains a release gate.

M6 plugin coverage exercises closed manifests, semantic compatibility, missing/cyclic dependencies, dependency-first activation and dependent-first deactivation, atomic failure, typed unknown/malformed/oversized setting rejection, namespaced permission safety, audits, deterministic contributions and redacted isolated optional failures. Full identity/content/Builder/theme regressions remain required. SQLite is exercised; real MySQL and TD-005 browser/assistive-technology verification remain release gates.

M7 coverage verifies integer money, catalogue visibility, atomic adjustments/ledger consistency, oversell rejection, idempotent reservation release and bounded repeated expiry. SQLite cannot establish real parallel row-lock behavior; real MySQL concurrency remains a release gate. Public browser viewport and assistive-technology interaction remains TD-005.


M8 coverage exercises guest capability isolation, cart merging/currency and hostile quantities, stale authoritative prices, deterministic integer rounding, coupon/tax/shipping rules, atomic reservation-backed checkout, idempotent replay/body mismatch, oversell, immutable snapshots, cancellation/expiry release and guest IDOR. SQLite cannot prove MySQL parallel row locks; real MySQL remains a release gate.


### Retained M6 CLI-server acceptance evidence (2026-08-16)

The acceptance environment used `/tmp/m6.sqlite`, `DB_CONNECTION=sqlite`, `SESSION_DRIVER=file`, a fixed test-only `APP_KEY`, and `php artisan serve --host=127.0.0.1 --port=8791`. `migrate:fresh --seed --force` created the isolated database; the dedicated `admin@pulse.test` fixture was attached to the canonical `super_admin` role only in that database. Tinker invoked `Totp::secret()`, persisted the encrypted secret/confirmation fields used by the enrollment implementation, and Reflection invoked the implementation's private `code` method for the current 30-second counter (no shell TOTP arithmetic).

Curl retained `/tmp/m6.cookies`, extracted each rendered `_token`, deliberately followed only the dashboard redirects, and asserted: login GET 200; login POST 302; MFA challenge GET 200; MFA POST 302; dashboard/plugin/settings GET 200; activation/settings/deactivation POST 302; configured `CLI proof contribution` present after activation and absent after deactivation; final dashboard 200. `/tmp/m6.stderr` and `storage/logs/laravel.log` were asserted empty. The isolated database, secret, cookies, pages and transcript were removed after inspection. This fresh evidence closes and replaces the old unexplained 403 note.

## M9 payment coverage

Payment tests exercise immutable commercial values, duplicate success fulfilment, cancellation/success reconciliation, refund monotonicity/no-restock, encrypted secret redaction, migrations and prior M7/M8 regressions. Adapter HTTP/signature tests use fakes without live credentials. Provider sandbox certification, real-MySQL race execution and browser/assistive-technology flows remain explicit environment gates rather than being inferred from fakes.
