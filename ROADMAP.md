# Pulse CMS roadmap

Milestones are dependency ordered and complete only with migrations, authorization, accessible interfaces, tests, docs and operational treatment.

1. **M0 Governance and runnable baseline** — permanent instructions/audit/recovery; restore locked dependencies; repair boot/schema blockers; baseline tests/build/smoke.
2. **M1 Identity and deny-by-default RBAC (complete)** — hardened authentication and lifecycle controls, normalized RBAC, safe user/role administration, capability-derived privileged-user TOTP MFA, recovery and backend enforcement, and the expanded adversarial identity authorization matrix are complete.
3. **M2 Pulse design system and admin shell (in progress: foundation and M1 workflow migration complete)** — tokens and reusable accessible components, custom safe toast/dialog/drawer system; responsive admin/public foundations; remove dead UI/assets.
4. **M3 Content verticals** — page/post lifecycle, taxonomy, media security/processing, menus, SEO and forms; bounded queries, audits and complete CRUD authorization.
5. **M4 Builder V4** — versioned validated nested schema, full block catalogue, reusable sections/templates, responsive preview, draft recovery, unsaved warnings and optimistic concurrency; restrict custom HTML.
6. **M5 Theme platform** — validated/versioned lifecycle, preview/activation/rollback/settings and polished Pulse Studio, Corporate and Commerce themes with graceful commerce absence.
7. **M6 Plugin runtime** — manifests, dependencies/compatibility, hooks/contributions, canonical permissions, validated settings, lifecycle and safe failure isolation.
8. **M7 Commerce catalogue/inventory** — products, variants/SKUs/media, integer money/currency, stock ledger/reservations and concurrency tests.
9. **M8 Cart, checkout and orders** — carts/addresses, coupons/tax/shipping, idempotent checkout, immutable order snapshots, state histories and admin management.
10. **M9 Payments/refunds** — gateway contract and real Stripe/PayPal/Flutterwave/Paystack adapters, encrypted configuration, canonical states, verified webhook inbox, refunds/disputes/reconciliation and exhaustive tests.
11. **M10 Operations** — append-only audits, notifications, health, protected redacted logs, queues/scheduler, performance/index review, exports and observability.
12. **M11 Installer and release readiness** — secure bootstrap, deployment/backup/restore/upgrade/rollback docs, end-to-end/accessibility/security checks, generated MySQL release SQL and clean-import boot validation; PostgreSQL artifact only if verified.

Credential-dependent live gateway certification and jurisdiction-specific tax/legal defaults may remain owner blockers, but adapter implementation, sandbox-ready configuration and all unblocked work continue.
