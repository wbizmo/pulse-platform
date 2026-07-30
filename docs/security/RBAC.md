# Role-based access control

## Implemented foundation

Pulse uses normalized users-to-roles and roles-to-permissions relationships. `App\Domain\Access\Permission` is the canonical permission catalogue; database permission rows and route abilities use its stable names. Existing `super_admin`, `admin`, `editor`, and `author` string assignments are migrated to protected system roles. Unknown legacy role values receive the least-privileged author role so that no migrated account gains authority unexpectedly.

Every administrative route except login and logout declares a permission through Laravel's `can` middleware. A user with no role or without the required permission receives HTTP 403. Disabled-account middleware runs before authorization. Super administrators bypass individual catalogue checks through `Gate::before`; this bypass is based on the normalized protected role, not the legacy string column. Navigation items use the same abilities and are hidden when unavailable.

## Current role matrix

| Capability | Super administrator | Administrator | Editor | Author |
|---|---:|---:|---:|---:|
| Dashboard | Yes | Yes | Yes | Yes |
| Pages/builder | Yes | Yes | Yes | No |
| Posts | Yes | Yes | Yes | Yes |
| Categories/tags | Yes | Yes | Yes | No |
| Media | Yes | Yes | Yes | Yes |
| Menus/SEO | Yes | Yes | Yes | No |
| Themes/plugins/settings/system | Yes | Yes | No | No |
| Users/roles | Yes | Yes | No | No |

User and role administration routes are not yet implemented, so their catalogue permissions do not currently expose an administrative operation. The next M1 slice must add validated administration actions, safe delegation, protected-system-role rules, last-super-administrator protection, audit records, accessible interfaces, and direct-access tests before roles can be changed through the application.


## MFA relationship

Any assignment granting one or more catalogue permissions makes the user privileged and activates MFA immediately. Removing all permissions removes that requirement; adding or changing a role cannot bypass it. Super administrators remain privileged through capability bypass rather than a display-name check.
