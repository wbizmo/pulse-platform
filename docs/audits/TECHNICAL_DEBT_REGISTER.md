# Technical debt register

Only consciously accepted, bounded compromises belong here. New entries require reason, risk, affected module, concrete remediation, roadmap milestone and release impact; close entries with evidence rather than deleting history.

| ID | Status | Reason | Risk | Module | Remediation | Milestone | Release impact |
|---|---|---|---|---|---|---|---|
| TD-001 | Partially resolved in M1 | The normalized RBAC foundation and route/UI enforcement are complete, and safe administration workflow are complete, but the legacy role column remains. | The compatibility column can diverge from normalized role assignments. | Identity/RBAC | Remove the legacy role column after compatibility verification. | M1 | Blocks production release. |
| TD-002 | Closed in M11 | Default seeding is production-safe, demo seeding rejects production, and installation requires an operator-supplied credential. | The inherited predictable-credential exposure is removed. | Installer/Identity | `pulse:install` accepts no default credential and writes a durable installation lock that refuses re-entry. | M1/M11 | Resolved. |
| TD-003 | Closed in M2 | All registered administration views now use the shared Pulse component and presentation contracts; the administration bridge has no consumers. | Public theme presentation remains separately scoped and domain hardening is still required. | UI | Continue domain hardening in M3–M6; public theme work belongs to M5. | M2 | Administration presentation migration resolved. |
| TD-005 | Open (environment) | No compatible browser runner has yet been verified in this checkout. | Responsive, focus, and assistive-technology defects may escape rendered-markup tests. | UI/Testing | Run Playwright/Chromium interaction and viewport coverage without committing browser binaries. | M2 | Blocks polished release. |
| TD-004 | Closed in M0 | Dependency directory and local environment were absent from checkout. | Runtime checks could not initially execute. | Tooling | Restored locked Composer/npm dependencies and added a deterministic PHPUnit key; baseline suite now passes. | M0 | Resolved. |
| TD-006 | Closed in M2 | A prior CLI-server smoke run reported a Symfony header-emission fatal without retaining its stderr, log, or generated-cache state. | A real runtime regression could have been hidden by in-process feature coverage. | UI/Testing | Rebuilt assets, cleared generated Laravel caches, reproduced the isolated request with captured channels, verified both file and array sessions, and added an anonymous login response regression. | M2 | Resolved; the clean runtime emits HTTP 200 with no stderr or Laravel exception. |

M1 privileged MFA introduced no newly accepted debt. QR rendering is intentionally not embedded: the one-time standards-based `otpauth://` URI and manual key support compatible authenticator/password-manager enrollment without adding a remote or secret-bearing image service.

| TD-007 | Partially resolved in M3 | Page/post featured media now uses restrictive nullable media foreign keys; unconvertible legacy post URLs are retained as inactive archival data. Page hierarchy and versioned builder documents/revisions remain assigned to M4. | Legacy URLs require an explicit operator-led conversion or disposal decision; revision restoration and hierarchical pages remain unavailable. | Content/Builder/Media | Complete any deployment-specific legacy URL conversion before dropping the archival column; implement compatible versioned revisions and hierarchy only with the M4 schema contract. | M3/M4 | Media reference integrity resolved; remaining M4 work blocks production release. |

Taxonomy hardening introduced no accepted taxonomy debt. Browser interaction remains tracked by TD-005; MySQL collation and migration behavior still require release-environment integration verification.

Media hardening introduced no newly accepted debt. Malware scanning is not claimed for the deliberately narrow, locally decoded raster-image allow-list; release storage configuration and MySQL behavior still require environment verification.

Menu hardening introduced no newly accepted debt. Navigation is deliberately flat because the current contract requires no hierarchy. Browser interaction remains tracked by TD-005, and real-MySQL migration behavior remains a release-environment gate.


SEO hardening introduced no accepted application debt. Legacy page/post social-image URL columns remain supported only as same-origin, validated compatibility fields; managed featured images and managed global defaults are the preferred path. Browser interaction remains TD-005 and real-MySQL migration verification remains a release-environment gate.

Forms hardening introduced no accepted application debt. CSV export, notifications, attachments, autoresponders and automation are deliberately absent because current product scope does not require them. Browser interaction remains TD-005 and real-MySQL migration verification remains a release-environment gate.

Builder V4 closes the Builder portion of TD-007 with a versioned validated document and managed media references. Historical unversioned Builder JSON is deliberately retained without automatic transformation because assigning stable identity or replacing legacy URL/HTML semantics would fabricate operator intent; it fails closed until an operator intentionally rebuilds or migrates it. Browser interaction remains TD-005 and real-MySQL migration verification remains a release-environment gate.

Theme M5 introduces no accepted application debt. Legacy CSS is inert recoverable data and never rendered. Browser/assistive-technology verification remains TD-005; real MySQL remains a release-environment gate.

Plugin M6 introduces no accepted application debt. Unsupported contribution types are intentionally absent rather than placeholders. Browser/assistive-technology verification remains TD-005 and real MySQL remains a release-environment gate.

Commerce M7 introduces no accepted application debt. SQLite sequential invariant coverage does not replace the real-MySQL concurrency gate, and browser/assistive-technology verification remains TD-005. The earlier M6 CLI MFA 403 has no retained script/transcript and therefore remains unclaimed rather than being misreported as resolved.


Commerce M8 introduces no accepted application debt. Real-MySQL parallel checkout/coupon/cancellation races remain a release-environment gate, and browser/assistive-technology verification remains TD-005. Order-linked inventory expiry is owned exclusively by the Order lifecycle.

Payments M9 introduces no accepted application debt. Provider live/sandbox certification was unavailable without owner credentials; real-MySQL race verification and TD-005 browser/assistive-technology checks remain release-environment gates. Official provider contracts were checked on 2026-08-16 and must be rechecked before release.

## M10 review

No historical financial/security evidence is subject to Operations pruning. TD-005 browser/assistive-technology qualification and genuine MySQL parallel concurrency remain open. Credential-dependent payment certification remains an owner/environment gate. Form-submission export is intentionally absent until a dedicated PII policy is approved; this is a product boundary rather than placeholder behavior.

## M11 qualification

TD-002 is resolved by the production-safe default seeder and production-rejecting demo seeder; `pulse:install` accepts no default credential and writes a durable completion lock. TD-005 remains an accessibility/browser qualification gate until it is genuinely closed. Genuine MySQL 8.0.46 clean migration and release-SQL import/schema comparison are evidenced. Parallel concurrency, disposable restore, M10 upgrade rehearsal, and provider credential certification remain release gates rather than claimed evidence; see `docs/release/RELEASE_CHECKLIST.md`.
