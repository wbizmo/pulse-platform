# Target architecture

Pulse remains a Laravel modular monolith, organized by cohesive capabilities: Identity/Access, Content, Builder, Media, Navigation, SEO/Forms, Themes, Plugins, Commerce, Payments, Audit/Notifications, and Operations/Installer. Modules share the deployment and transactional database while exposing explicit application contracts and events rather than reaching into each other's controllers or views.

Requests flow through security middleware, typed Form Requests and policies to a small controller, then an application action. Domain invariants live in domain services, enums/value objects and aggregates; persistence uses focused Eloquent queries. Provider APIs sit behind gateway contracts/adapters. Slow effects use idempotent queued jobs. Audit events are append-only and redact sensitive context. Configuration is read through Laravel config and secrets are encrypted at rest.

RBAC has a canonical permission catalogue, many-to-many user roles and role permissions, protected system-role invariants and safe delegation. Plugins contribute manifests, permissions, routes, services, events, jobs, views, blocks, commands and widgets through a failure-isolated registry. Themes contribute presentation and typed settings only; commerce logic remains within Commerce.

Builder documents use a validated, versioned schema with stable node IDs, nested container rules, reusable/template references, responsive settings and an optimistic-lock version. Commerce uses explicit state machines, integer money, immutable order snapshots and locked inventory reservations. Payments use a gateway registry, idempotent commands, verified webhook inbox and reconciliation.

Operational architecture includes queues, scheduler, health checks, structured/redacted logs, protected diagnostics, notifications, backups, upgrade/rollback paths and release artifacts. Migrations remain canonical. `database/releases/pulse_cms_mysql.sql` is generated and clean-import tested at release; PostgreSQL SQL is published only after genuine compatibility verification.


The presentation boundary uses Pulse design tokens and small Blade components. A shared administration shell owns authorized navigation, responsive drawer, account controls, global feedback, and dialog regions; module views compose it rather than duplicating layout or interaction code.

Content lifecycle mutations use typed states, Form Requests, transactional actions, conditional scheduled transitions, signed previews, centralized public scopes, and optimistic versions. Scheduler workers remain bounded and idempotent.

Taxonomy mutations use normalized domain values, database uniqueness, explicit assignment authority, transactional audit/cache effects, and dependency-aware deletion. Public archives consume the same content visibility scope as primary routes.

The completed M3 Forms boundary establishes the target pattern for schema-driven public input: closed declarative configuration, server-compiled validation, immutable historical snapshots, conservative retention, metadata-only auditing, and independently authorized personal-data access.

M5 establishes immutable theme manifests, closed settings, centralized resolution, a reversible lifecycle and presentation-only renderers. Third-party contributions remain M6 scope.

## M6 plugin boundary implemented

The plugin target now has an authoritative code manifest registry, deterministic dependency graph, lifecycle application actions, validated settings and explicit contribution interfaces. Future extension types must be introduced as closed contracts with authorization and failure-boundary tests rather than database callback/class/path strings.

## Commerce progression

M7 supplies catalogue and single-pool inventory infrastructure. M8 may consume reservation actions for carts, checkout and orders; M9 may add gateway-neutral payments. Neither later concern may mutate balances outside Commerce actions.


M8 establishes the transaction aggregate and explicit awaiting-payment boundary consumed by planned M9 payment orchestration; providers may not mutate carts, stock or orders directly.
