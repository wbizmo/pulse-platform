# Changelog

All notable changes are recorded here. The project is pre-release and follows milestone-based development.

## Unreleased

### Added
- Repository-level engineering governance, product/security/testing contracts, architecture baseline and target, initial audit, debt register, dependency-ordered roadmap and recovery checkpoint.
- Added paginated user and role administration with validated workflows, safe delegation, protected system roles, final-super-administrator safeguards, and append-only security audit records.
- Added the canonical RBAC permission catalogue, normalized role and permission schema, legacy-role migration, system role matrix, Gate integration, authorization tests, and permission-aware admin navigation.

### Fixed
- Made frontend asset compilation deterministic by removing its build-time remote font dependency.

### Security
- Added login throttling, active-account checks at login and on authenticated requests, session invalidation for disabled users, and authentication regression coverage.
- Enforced a specific permission on every administrative capability, with deny-by-default behavior for accounts without assigned authority and a normalized super-administrator bypass.
