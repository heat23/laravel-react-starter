# ADR 0005: Admin vs Super-Admin Delegation

## Status
Accepted

## Context
The admin panel has two middleware roles: `admin` and `super_admin`. Over time, identity-sensitive mutations (editing user name/email, toggling admin status, impersonating users) accumulated under the plain `admin` role alongside read-only and non-identity mutations. This created a privilege escalation risk: any admin could modify another admin's identity or elevate their own privileges.

## Decision
Establish a clear delegation boundary:

| Role | Allowed actions |
|------|----------------|
| `admin` | Read-only views, audit logs, reports, feedback management, cache clearing, health checks, non-identity write actions (e.g., resolve roadmap items, send password reset emails) |
| `super_admin` | Identity mutations (edit user name/email/timezone), toggle admin status, toggle active status, bulk deactivate/restore, feature flag overrides, system configuration |

Concretely, the following routes require `super_admin` middleware:
- `PATCH /admin/users/{user}` (`users.update`) — editing user identity
- `PATCH /admin/users/{user}/toggle-admin` (`users.toggle-admin`)
- `PATCH /admin/users/{user}/toggle-active` (`users.toggle-active`)
- `POST /admin/users/bulk-deactivate` (`users.bulk-deactivate`)
- `POST /admin/users/bulk-restore` (`users.bulk-restore`)

The frontend edit form on the Admin User Show page is hidden when `auth.user.is_super_admin` is false.

## Consequences

### Positive
- Identity mutations require explicit elevation, reducing blast radius of compromised admin accounts
- The boundary is enforced at the route level (middleware), not just the UI layer
- Unauthorized attempts return 403 with no information leakage

### Negative
- Regular admins lose the ability to edit user details directly (must escalate to super_admin)
- Behavior change is intentional and not backwards-compatible; existing admin users are unaffected (they retain their current role)

## References
- `app/Http/Middleware/EnsureIsSuperAdmin.php`
- `routes/admin.php`
