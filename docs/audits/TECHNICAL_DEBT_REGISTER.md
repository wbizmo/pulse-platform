# Technical debt register

Only consciously accepted, bounded compromises belong here. New entries require reason, risk, affected module, concrete remediation, roadmap milestone and release impact; close entries with evidence rather than deleting history.

| ID | Status | Reason | Risk | Module | Remediation | Milestone | Release impact |
|---|---|---|---|---|---|---|---|
| TD-001 | Partially resolved in M1 | The normalized RBAC foundation and route/UI enforcement are complete, and safe administration workflow are complete, but the legacy role column remains. | The compatibility column can diverge from normalized role assignments. | Identity/RBAC | Remove the legacy role column after compatibility verification. | M1 | Blocks production release. |
| TD-002 | Open (inherited) | Existing seed data uses public predictable passwords. | Account compromise if executed outside disposable development. | Installer/Identity | Replace with environment-gated secure bootstrap/installer and randomized one-time credential flow. | M1/M11 | Blocks production release. |
| TD-003 | Open (inherited) | Browser-delivered CSS/JS and Blade UI predate a documented component system. | Inconsistent accessibility and unsafe/disconnected interactions. | UI | Introduce tokens/components/toasts and migrate screens slice-by-slice. | M2 | Blocks polished release. |
| TD-004 | Closed in M0 | Dependency directory and local environment were absent from checkout. | Runtime checks could not initially execute. | Tooling | Restored locked Composer/npm dependencies and added a deterministic PHPUnit key; baseline suite now passes. | M0 | Resolved. |

M1 privileged MFA introduced no newly accepted debt. QR rendering is intentionally not embedded: the one-time standards-based `otpauth://` URI and manual key support compatible authenticator/password-manager enrollment without adding a remote or secret-bearing image service.
