# Technical debt register

Only consciously accepted, bounded compromises belong here. New entries require reason, risk, affected module, concrete remediation, roadmap milestone and release impact; close entries with evidence rather than deleting history.

| ID | Status | Reason | Risk | Module | Remediation | Milestone | Release impact |
|---|---|---|---|---|---|---|---|
| TD-001 | Open (inherited) | Existing users store a single role string and routes only require authentication. | Critical privilege escalation and unauthorized administration. | Identity/RBAC | Normalize roles/permissions, migrate legacy assignments, enforce catalogue through policies/middleware and matrix tests. | M1 | Blocks production release. |
| TD-002 | Open (inherited) | Existing seed data uses public predictable passwords. | Account compromise if executed outside disposable development. | Installer/Identity | Replace with environment-gated secure bootstrap/installer and randomized one-time credential flow. | M1/M11 | Blocks production release. |
| TD-003 | Open (inherited) | Browser-delivered CSS/JS and Blade UI predate a documented component system. | Inconsistent accessibility and unsafe/disconnected interactions. | UI | Introduce tokens/components/toasts and migrate screens slice-by-slice. | M2 | Blocks polished release. |
| TD-004 | Closed in M0 | Dependency directory and local environment were absent from checkout. | Runtime checks could not initially execute. | Tooling | Restored locked Composer/npm dependencies and added a deterministic PHPUnit key; baseline suite now passes. | M0 | Resolved. |
