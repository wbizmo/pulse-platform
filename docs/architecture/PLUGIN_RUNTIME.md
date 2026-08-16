# Plugin runtime

## Trust and manifest contract

M6 is a closed first-party extension runtime, not a marketplace or code installer. `PluginManifestRegistry` is the sole authority for executable identity: slug, name, semantic version, Pulse compatibility, dependencies, conflicts, provides, namespaced permissions, typed settings, contribution classes, and runtime class. Database rows persist lifecycle and settings only. Unknown, malformed, incompatible, cyclic, self-dependent, unsafe-permission, and unknown-contribution manifests fail closed. No uploaded archive, database class name, dynamic include, `eval`, arbitrary route, view, JavaScript, CSS, or PHP is executed.

The registry uses deterministic slug ordering plus dependency-first topological resolution. M6 accepts exact semantic versions and caret constraints. Activation refuses missing/inactive/incompatible dependencies and declared active conflicts. Deactivation refuses while an active dependent exists; no surprise cascade occurs.

## Lifecycle and persistence

`ChangePluginState` performs activation and deactivation in database transactions with locked lifecycle rows. Activation validates the code manifest, persisted version, compatibility, current typed settings, dependencies and conflicts before changing state; synchronizes only manifest-owned namespaced permissions; writes a bounded metadata audit; and invalidates the request runtime. Deactivation preserves settings, checks dependents, audits, and invalidates runtime state. Failures roll back without a half-active plugin.

The legacy `is_active` column represents the intentionally small inactive/active state machine. Existing legacy rows are retained but made inactive by migration, are absent from the runtime/admin catalogue, and preserve data for operator-led retirement. Fresh seeding creates only Editorial Notes and Publishing Insights. Blog, Forms, SEO, Security, Builder and Themes remain non-disableable core capabilities; commerce/payment claims remain absent until their milestones.

## Settings and permissions

Settings accept only manifest keys and the explicit boolean, bounded safe string, enum, and bounded integer types. Arrays, control characters, markup, oversized strings and invalid choices are rejected. M6 proof plugins need no secrets or media/path/URL input. Audits record setting keys, never values.

Plugin permissions must start with `plugin.<manifest-slug>.`, cannot impersonate core permission names, and are synchronized non-destructively on activation. Existing role assignments are not reseeded or detached. Deactivation removes the active runtime capability but retains permission rows/role joins so reactivation does not erase deliberate delegation. Plugins cannot define a super-administrator bypass.

## Contributions and failure isolation

M6 proves two explicit code contracts: ordered dashboard widgets and typed named hooks. Contributions are instantiated only from authoritative PHP manifests. `PluginRuntime` reconciles active persisted rows against version-compatible manifests in dependency order and fails closed for corrupt state. Optional contribution invocation has a narrow boundary: the failing contribution is skipped and a warning records only plugin slug, contribution/event type, and exception class. Messages, settings, stack traces and secrets are not logged. Core authorization/data-integrity workflows are outside this catch boundary.

Editorial Notes contributes a configurable escaped dashboard reminder. Publishing Insights depends on Editorial Notes and contributes a bounded publishing widget plus a deterministic dashboard hook. Neither duplicates a core module or introduces commerce. Plugin routes, arbitrary callbacks, commands, jobs, Builder blocks and public views are deliberately unsupported until a real first-party use case can prove a safe contract.

## Operations

Lifecycle endpoints remain under the existing authenticated, active, verified, `plugins.manage`, privileged-MFA middleware group and Laravel CSRF protection. The admin catalogue renders only registry manifests and explains versions, status, dependencies, compatibility and provides. Runtime settings and widget output are escaped by Blade.

SQLite migration/fresh seed and application tests are M6 evidence. Real MySQL and browser/assistive-technology interaction remain release gates; SQLite and CLI smoke do not substitute for them.
