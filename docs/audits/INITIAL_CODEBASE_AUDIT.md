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

## M3 content lifecycle follow-up

The initial page/post implementation mixed inline validation and persistence, accepted arbitrary status/taxonomy/time values, leaked draft pages through the catch-all route, omitted effective-time checks from posts and sitemap, offered no scheduler, audit, signed preview, or lost-update protection, and used unbounded taxonomy selectors. The lifecycle slice replaces those paths with focused requests/actions, a shared public scope, conditional scheduled publishing, bounded selectors, signed private preview routes, optimistic versions, audit events, and composite publication indexes.

## M3 taxonomy follow-up

The inherited taxonomy controllers performed inline validation and unaudited direct writes, allowed case-equivalent names, relied only on slug uniqueness, loaded all administration rows, silently detached relationships on deletion, and exposed no public archive contract. Post editors could assign taxonomy with only post permission. The hardened slice adds canonical normalization and database constraints, focused requests/actions, explicit assignment authority, bounded identifiers, dependency-blocking transactional deletion, usage counts/pagination, audits, and visibility-scoped public archives. Categories remain flat because no product behavior requires hierarchy.

## M3 menu follow-up

The inherited menu controller validated inline, mutated models directly, loaded every menu and page, copied page slugs into stale URL fields, accepted arbitrary custom URLs and sort values, and exposed unscoped item deletion. Public rendering selected an arbitrary active menu and could link unpublished pages. The hardened flat-menu slice adds focused requests/actions, bounded queries, singleton main/footer activation, safe links, parent-scoped item mutations, atomic reorder, restrictive page references, audits, and centralized publication-aware loading. Hierarchy remains deliberately out of scope because no current product behavior requires it.


## M3 SEO follow-up

The inherited endpoint persisted arbitrary request keys, mixed workflow into controllers, accepted broad URL input, calculated metadata in Blade, emitted generic schema, corrupted absolute robots Sitemap lines, and loaded unbounded sitemap collections with invented timestamps. The hardened slice adds explicit requests/actions and authority, safe canonical policy, managed global images, resolved context metadata, deterministic robots output, and cached lazy/bounded sitemap generation with public archives and truthful dates.

## M3 Forms follow-up

No first-party forms persistence or authorization vertical existed. M3 adds normalized forms, bounded closed-schema fields, immutable durable submissions, strict public validation and abuse controls, conservative retention, paginated administration, metadata-only audits, and independent MFA-protected Forms authority. Export and notifications were deliberately deferred because current product scope does not require either for this initial vertical.
