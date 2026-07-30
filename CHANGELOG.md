# Changelog

All notable changes are recorded here. The project is pre-release and follows milestone-based development.

## Unreleased

### Added
- Added the tokenized Pulse design system, permission-aware responsive administration shell, accessible Blade component foundations, safe queued toasts, and custom destructive-action confirmation dialog; migrated M1 identity, MFA, profile, user, role, and dashboard interfaces.
- Repository-level engineering governance, product/security/testing contracts, architecture baseline and target, initial audit, debt register, dependency-ordered roadmap and recovery checkpoint.
- Added paginated user and role administration with validated workflows, safe delegation, protected system roles, final-super-administrator safeguards, and append-only security audit records.
- Added the canonical RBAC permission catalogue, normalized role and permission schema, legacy-role migration, system role matrix, Gate integration, authorization tests, and permission-aware admin navigation.
- Added signed and throttled email verification, non-enumerating password recovery, password confirmation, profile management, and owner-bounded database session visibility and revocation.
- Added permission-derived privileged-user TOTP MFA with encrypted secrets, one-way single-use recovery codes, replay/rate-limit controls, password-confirmed lifecycle operations, audited administrator recovery, and accessible enrollment/challenge interfaces.

### Fixed
- Completed the M2 administration presentation migration for media, menus, builder, themes and customizer, plugins and settings, site settings, and SEO; retired administration `pulse-*` bridge consumers while preserving authorized workflows, shared confirmation dialogs, and safe toasts.
- Migrated page and post administration lists and editors from the legacy presentation bridge to reusable Pulse page-header, card, table, form, badge, action, pagination, empty-state, and confirmation components.
- Migrated category and tag administration from the legacy presentation bridge to direct Pulse form, card, action, empty-state, and confirmation components, with unique accessible control identifiers for repeated edit forms.
- Applied the Pulse token and responsive component contracts to remaining legacy administration module layouts, replaced native confirmation prompts across content and builder workflows with the shared accessible dialog, and removed an empty malformed frontend asset.
- Made frontend asset compilation deterministic by removing its build-time remote font dependency.

### Security
- Added login throttling, active-account checks at login and on authenticated requests, session invalidation for disabled users, and authentication regression coverage.
- Enforced a specific permission on every administrative capability, with deny-by-default behavior for accounts without assigned authority and a normalized super-administrator bypass.
- Required verified email addresses for administrative capabilities, step-up password confirmation for credential and session changes, active-account reset eligibility, session IDOR protection, and audit records for identity security events.
- Enforced completed MFA at the backend before every privileged capability and expanded adversarial identity coverage across anonymous, disabled, unverified, ordinary, newly privileged, and challenged users.

### M3 content lifecycle
- Hardened page and post lifecycle states, centralized hostile-input validation and mutation, public visibility scopes, atomic scheduled publication, signed private previews, normalized/reserved slug protection, optimistic locking, bounded taxonomy choices, audit records, publication indexes, and adversarial lifecycle coverage.
