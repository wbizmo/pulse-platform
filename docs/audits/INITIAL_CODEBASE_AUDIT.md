# Initial codebase audit

Date: 2026-07-30. Scope: repository structure, routes, controllers, models, migrations, seeders, views/assets, dependency manifests and tests.

## Findings

| Severity | Finding | Evidence / impact | Direction |
|---|---|---|---|
| Critical | Admin lacks authorization | Every admin capability is inside only `auth`; any authenticated user can mutate settings, plugins, themes and content. | Canonical RBAC, middleware/policies, matrix tests. |
| High (resolved M0/M1) | Disabled users can authenticate/remain active | Active-account checks now reject login and invalidate protected sessions; password recovery also excludes disabled accounts. | Retain regression coverage. |
| High (resolved M0) | Login has no explicit throttle | Login now uses a rate-limited request with non-enumerating errors. | Retain regression coverage. |
| High | Seeded predictable accounts | Seeder creates four known addresses with password `password`. | Restrict development fixtures; secure installer/bootstrap workflow. |
| High | Required commerce/payment/audit capabilities absent | No models/migrations/services/routes for these boundaries. | Dependency-ordered modular slices. |
| Medium (substantially resolved M1) | Single role string is not RBAC | Normalized RBAC and delegation invariants are implemented; the legacy compatibility column remains. | Remove the legacy column after compatibility verification. |
| Medium | Controllers mix validation/workflow | Inline request validation and direct model operations dominate CRUD. | Introduce requests/actions/policies per slice. |
| Medium | Builder/runtime lifecycle incomplete | Existing builder/plugin/theme UI does not meet required schema, lifecycle, isolation and concurrency guarantees. | Preserve useful behavior while versioning contracts. |
| Medium (partially resolved M1) | Test suite is skeletal | Identity and access now have security-first feature coverage; other modules remain largely uncovered. | Add module suites with each dependency-ordered slice. |
| Medium | Operational/release surface absent | No installer, audit/log access, production health, release SQL validation. | Add after identity and core domain foundations. |
| Low (partially resolved M2) | Misleading/dead UI and asset artifacts | The disconnected shell Site Health link and obsolete direct admin bundles were removed; `public/js/frontend.jsphp` is still suspicious and boilerplate product metadata remains. | Remove or implement during UI/operations cleanup. |
| Low | Asset compilation required network font retrieval | Vite configured a Bunny-hosted Instrument Sans fetch, preventing deterministic/offline builds. | Replaced build-time retrieval with the local system font stack in M0. |

## Environment and assets

The checked-out app declares PHP 8.3, Laravel 13.8 and PHPUnit 12.5. Dependencies and `.env` were absent during the baseline inspection. Git is on `work`, configured as `Codex <codex@openai.com>`, with no remote. Existing implementation is retained and evolved incrementally; this audit does not assert runtime success before dependencies and tests execute.

## M1 identity follow-up

The identity recovery/session checkpoint was verified at merge `eed503a` (focused commit `e207b53`). The previously absent privileged MFA boundary is now implemented with capability-derived enforcement, encrypted TOTP secrets, hashed recovery codes, replay/throttle controls, audited recovery, and an expanded adversarial authorization matrix.
