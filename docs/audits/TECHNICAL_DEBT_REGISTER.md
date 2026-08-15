# Technical debt register

Only consciously accepted, bounded compromises belong here. New entries require reason, risk, affected module, concrete remediation, roadmap milestone and release impact; close entries with evidence rather than deleting history.

| ID | Status | Reason | Risk | Module | Remediation | Milestone | Release impact |
|---|---|---|---|---|---|---|---|
| TD-001 | Partially resolved in M1 | The normalized RBAC foundation and route/UI enforcement are complete, and safe administration workflow are complete, but the legacy role column remains. | The compatibility column can diverge from normalized role assignments. | Identity/RBAC | Remove the legacy role column after compatibility verification. | M1 | Blocks production release. |
| TD-002 | Open (inherited) | Existing seed data uses public predictable passwords. | Account compromise if executed outside disposable development. | Installer/Identity | Replace with environment-gated secure bootstrap/installer and randomized one-time credential flow. | M1/M11 | Blocks production release. |
| TD-003 | Closed in M2 | All registered administration views now use the shared Pulse component and presentation contracts; the administration bridge has no consumers. | Public theme presentation remains separately scoped and domain hardening is still required. | UI | Continue domain hardening in M3–M6; public theme work belongs to M5. | M2 | Administration presentation migration resolved. |
| TD-005 | Open (environment) | No compatible browser runner has yet been verified in this checkout. | Responsive, focus, and assistive-technology defects may escape rendered-markup tests. | UI/Testing | Run Playwright/Chromium interaction and viewport coverage without committing browser binaries. | M2 | Blocks polished release. |
| TD-004 | Closed in M0 | Dependency directory and local environment were absent from checkout. | Runtime checks could not initially execute. | Tooling | Restored locked Composer/npm dependencies and added a deterministic PHPUnit key; baseline suite now passes. | M0 | Resolved. |
| TD-006 | Closed in M2 | A prior CLI-server smoke run reported a Symfony header-emission fatal without retaining its stderr, log, or generated-cache state. | A real runtime regression could have been hidden by in-process feature coverage. | UI/Testing | Rebuilt assets, cleared generated Laravel caches, reproduced the isolated request with captured channels, verified both file and array sessions, and added an anonymous login response regression. | M2 | Resolved; the clean runtime emits HTTP 200 with no stderr or Laravel exception. |

M1 privileged MFA introduced no newly accepted debt. QR rendering is intentionally not embedded: the one-time standards-based `otpauth://` URI and manual key support compatible authenticator/password-manager enrollment without adding a remote or secret-bearing image service.

| TD-007 | Partially resolved in M3 | Page/post featured media now uses restrictive nullable media foreign keys; unconvertible legacy post URLs are retained as inactive archival data. Page hierarchy and versioned builder documents/revisions remain assigned to M4. | Legacy URLs require an explicit operator-led conversion or disposal decision; revision restoration and hierarchical pages remain unavailable. | Content/Builder/Media | Complete any deployment-specific legacy URL conversion before dropping the archival column; implement compatible versioned revisions and hierarchy only with the M4 schema contract. | M3/M4 | Media reference integrity resolved; remaining M4 work blocks production release. |

Taxonomy hardening introduced no accepted taxonomy debt. Browser interaction remains tracked by TD-005; MySQL collation and migration behavior still require release-environment integration verification.

Media hardening introduced no newly accepted debt. Malware scanning is not claimed for the deliberately narrow, locally decoded raster-image allow-list; release storage configuration and MySQL behavior still require environment verification.

Menu hardening introduced no newly accepted debt. Navigation is deliberately flat because the current contract requires no hierarchy. Browser interaction remains tracked by TD-005, and real-MySQL migration behavior remains a release-environment gate.
