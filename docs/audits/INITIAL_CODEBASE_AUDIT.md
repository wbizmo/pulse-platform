# Initial codebase audit

Date: 2026-07-30. Scope: repository structure, routes, controllers, models, migrations, seeders, views/assets, dependency manifests and tests.

## Findings

| Severity | Finding | Evidence / impact | Direction |
|---|---|---|---|
| Critical | Admin lacks authorization | Every admin capability is inside only `auth`; any authenticated user can mutate settings, plugins, themes and content. | Canonical RBAC, middleware/policies, matrix tests. |
| High | Disabled users can authenticate/remain active | `status` exists but login/authenticated routes do not enforce it. | Reject at login and on every protected request; revoke sessions. |
| High | Login has no explicit throttle | Custom login calls `Auth::attempt` without limiter. | Rate-limited Form Request/action with regression tests. |
| High | Seeded predictable accounts | Seeder creates four known addresses with password `password`. | Restrict development fixtures; secure installer/bootstrap workflow. |
| High | Required commerce/payment/audit capabilities absent | No models/migrations/services/routes for these boundaries. | Dependency-ordered modular slices. |
| Medium | Single role string is not RBAC | No permission catalogue/pivots/policies or delegation invariants. | Normalize roles and permissions, migrate legacy roles safely. |
| Medium | Controllers mix validation/workflow | Inline request validation and direct model operations dominate CRUD. | Introduce requests/actions/policies per slice. |
| Medium | Builder/runtime lifecycle incomplete | Existing builder/plugin/theme UI does not meet required schema, lifecycle, isolation and concurrency guarantees. | Preserve useful behavior while versioning contracts. |
| Medium | Test suite is skeletal | Only framework example tests exist. | Establish security-first feature tests, then module suites. |
| Medium | Operational/release surface absent | No installer, audit/log access, production health, release SQL validation. | Add after identity and core domain foundations. |
| Low | Misleading/dead UI and asset artifacts | Site Health points to `#`; `public/js/frontend.jsphp` is suspicious; boilerplate product metadata remains. | Remove or implement during UI/operations cleanup. |
| Low | Asset compilation required network font retrieval | Vite configured a Bunny-hosted Instrument Sans fetch, preventing deterministic/offline builds. | Replaced build-time retrieval with the local system font stack in M0. |

## Environment and assets

The checked-out app declares PHP 8.3, Laravel 13.8 and PHPUnit 12.5. Dependencies and `.env` were absent during the baseline inspection. Git is on `work`, configured as `Codex <codex@openai.com>`, with no remote. Existing implementation is retained and evolved incrementally; this audit does not assert runtime success before dependencies and tests execute.
