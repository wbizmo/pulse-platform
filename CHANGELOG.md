# Changelog

All notable changes are recorded here. The project is pre-release and follows milestone-based development.

## Unreleased

### Added
- Added the tokenized Pulse design system, permission-aware responsive administration shell, accessible Blade component foundations, safe queued toasts, and custom destructive-action confirmation dialog; migrated M1 identity, MFA, profile, user, role, and dashboard interfaces.
- Repository-level engineering governance, product/security/testing contracts, architecture baseline and target, initial audit, debt register, dependency-ordered roadmap and recovery checkpoint.
- Added paginated user and role administration with validated workflows, safe delegation, protected system roles, final-super-administrator safeguards, and append-only security audit records.
- Added the canonical RBAC permission catalogue, normalized role and permission schema, legacy-role migration, system role matrix, Gate integration, authorization tests, and permission-aware admin navigation.
- Added signed and throttled email verification, non-enumerating password recovery, password confirmation, profile management, and owner-bounded database session visibility and revocation.
- Added permission-derived privileged-user TOTP MFA with encrypted secrets, one-way single-use recovery codes, replay/rate-limit controls, password-confirmed lifecycle operations, audited administrator recovery, and accessible enrollment/challenge interfaces.

### Fixed
- Repaired the Forms submissions inbox Blade structure exposed by the deferred real CLI-server acceptance smoke; the authenticated inbox now compiles and renders under the production view compiler.
- Completed the M2 administration presentation migration for media, menus, builder, themes and customizer, plugins and settings, site settings, and SEO; retired administration `pulse-*` bridge consumers while preserving authorized workflows, shared confirmation dialogs, and safe toasts.
- Migrated page and post administration lists and editors from the legacy presentation bridge to reusable Pulse page-header, card, table, form, badge, action, pagination, empty-state, and confirmation components.
- Migrated category and tag administration from the legacy presentation bridge to direct Pulse form, card, action, empty-state, and confirmation components, with unique accessible control identifiers for repeated edit forms.
- Applied the Pulse token and responsive component contracts to remaining legacy administration module layouts, replaced native confirmation prompts across content and builder workflows with the shared accessible dialog, and removed an empty malformed frontend asset.
- Made frontend asset compilation deterministic by removing its build-time remote font dependency.

### Security
- Added login throttling, active-account checks at login and on authenticated requests, session invalidation for disabled users, and authentication regression coverage.
- Enforced a specific permission on every administrative capability, with deny-by-default behavior for accounts without assigned authority and a normalized super-administrator bypass.
- Required verified email addresses for administrative capabilities, step-up password confirmation for credential and session changes, active-account reset eligibility, session IDOR protection, and audit records for identity security events.
- Enforced completed MFA at the backend before every privileged capability and expanded adversarial identity coverage across anonymous, disabled, unverified, ordinary, newly privileged, and challenged users.

### M3 media
- Added a managed raster-image domain with bounded hostile-upload validation, decoded dimensions, opaque filesystem paths, metadata editing, pagination, audits, dependency-protected deletion, and SVG/executable rejection.
- Replaced page/post featured-image entry with nullable, restrictive media foreign keys, bounded editor choices, managed public rendering, and conservative retention of unconverted legacy post URLs as inactive archival data.

### M3 content lifecycle
- Hardened page and post lifecycle states, centralized hostile-input validation and mutation, public visibility scopes, atomic scheduled publication, signed private previews, normalized/reserved slug protection, optimistic locking, bounded taxonomy choices, audit records, publication indexes, and adversarial lifecycle coverage.
- Hardened the taxonomy domain with Unicode-aware normalized names, separate category/tag slug namespaces, database uniqueness, bounded assignment, independent assignment permission, dependency-safe transactional deletion, audits, paginated usage-aware administration, and public visibility-safe category/tag archives.

### M3 menu and media follow-up
- Made media deletion final-state audits truthful: storage failures retain the record and append a failure event, while completed events follow object removal and transactional row deletion.
- Hardened flat navigation with singleton main/footer activation, bounded queries, focused requests/actions, parent-scoped item CRUD, atomic reorder, safe custom links, page-derived URLs, restrictive page references, publication-aware rendering, audits, and protected new-tab links.

### M3 SEO
- Hardened global SEO behind an explicit allow-listed request/action contract with bounded validation, transactional persistence, cache invalidation, managed-media selection, and bounded audits.
- Enforced `seo.manage` independently on page/post metadata, restricted canonical overrides to same-origin HTTP(S), centralized escaped context-aware metadata and JSON-LD resolution, and added article/archive/pagination behavior.
- Rebuilt robots and sitemap output with normalized deterministic text, safe absolute sitemap directives, truthful timestamps, homepage deduplication, public taxonomy URLs, lazy bounded queries, protocol URL limits, caching, and visibility-safe output.

### M3 Forms
- Added first-party forms, closed-schema ordered fields, durable immutable submission snapshots, active public rendering, strict server-side validation, CSRF, honeypot and anonymous rate limiting.
- Added MFA-protected `forms.manage` administration, bounded submission inbox/detail views, conservative retention, parent-scoped atomic field operations, audits that exclude submitted values, and adversarial coverage.

### M4 Builder V4
- Replaced arbitrary Builder JSON persistence with a server-authoritative schema-v1 decoder, stable UUID node identity, closed block registry, strict known-key validation, bounded trees/collections/responsive tokens, and safe URL/video policies.
- Added atomic Page-version concurrency, metadata-only audits, managed-image reference validation and deletion protection, persisted snapshot templates, secure shared public/preview rendering, and fail-closed legacy handling that never executes stored HTML.
- Rebuilt the editor contract around server-provided metadata, keyboard-operable ordering/duplication/deletion, responsive controls, secure preview, dirty-state navigation protection, and bounded page/version-specific local recovery.

### M5 Theme Platform
- Added the authoritative versioned Studio, Corporate and Commerce registry, closed typed settings, managed branding media, centralized safe resolution and distinct presentation.
- Added transactional singleton activation, settings snapshots, audited rollback, private non-mutating preview and non-destructive legacy retirement.
- Removed arbitrary theme CSS, font and URL customization from runtime behavior and protected theme media dependencies.

### M6 Plugin runtime
- Added a closed semantic-versioned first-party manifest registry, deterministic dependency graph, transactional audited lifecycle, canonical namespaced permission synchronization and runtime cache invalidation.
- Replaced arbitrary plugin settings with typed closed schemas and replaced the fake catalogue with two genuine non-commerce proof plugins using explicit dashboard-widget and hook contracts.
- Retired legacy fake/core/future plugin rows non-destructively, isolated optional contribution failures with redacted logging, and restored clean SQLite fresh seeding.

### M7 Commerce catalogue and inventory
- Added the independent Commerce product category, product, variant and managed-gallery model with typed lifecycle, normalized globally unique SKUs, bounded option snapshots, and variant-authoritative integer-minor-unit prices with explicit allow-listed currencies.
- Added transactionally materialized inventory balances, an append-only ledger, locked adjustments, concurrency-safe reservations, idempotent release/consume/expiry transitions, and a bounded scheduled expiration command.
- Added independently MFA-protected catalogue and inventory administration, managed-media deletion protection, and real theme-runtime public catalogue/category/product presentation without cart, checkout, order, or payment controls.

### M8 Cart, checkout and orders
- Added opaque server-side guest carts, authoritative variant repricing, one-currency line mutations, deterministic coupon/tax/shipping totals, idempotent atomic checkout through M7 reservations, immutable awaiting-payment orders, capability-protected confirmation, state history, cancellation/expiry, administration and accessible theme-neutral storefront views.
- Added order/rule RBAC authorities and documented the payment-free M9 handoff; payment gateways, paid transitions and refunds remain absent.

### M9 Payments and refunds
- Added the immutable-order Payment aggregate, retryable attempts, closed first-party Stripe/PayPal/Flutterwave/Paystack adapters, encrypted configuration and explicit currency availability.
- Added verified replay-protected webhook intake, server verification, atomic paid Order/inventory/coupon fulfilment, payment-aware terminal transitions, idempotent partial/full refunds, disputes, bounded reconciliation, private customer UX and MFA/RBAC administration.

### M10 Operations
- Added minimal public liveness and a protected Operations area with typed readiness checks, scheduler heartbeat, bounded database-queue triage, payment operational summaries, in-app transition alerts, append-only audit administration, centralized logging redaction, correlation IDs, protected bounded logs, private formula-safe exports and expiry pruning.
- Hardened scheduled ownership with overlap locks, conservative configured-gateway payment reconciliation, intentional daily log retention, bounded command output and evidence-based audit/webhook indexes.
