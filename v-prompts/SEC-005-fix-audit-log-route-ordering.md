# SEC-005: Fix audit-logs route ordering bug

## Problem
In `routes/admin.php`, the `/audit-logs/export` route (line 136) is registered AFTER the `/audit-logs/{auditLog}` wildcard route (line 139). Laravel matches routes in registration order, so requesting `/admin/audit-logs/export` will match `{auditLog}` with the value "export", causing a ModelNotFoundException.

## Fix
Move the export route BEFORE the wildcard route in `routes/admin.php`.

## Prompt
```
/v Fix audit-logs route ordering in routes/admin.php: move the audit-logs/export route (currently line 136-138) BEFORE the audit-logs/{auditLog} show route (line 139). This matches the pattern used by users, feedback, and contact-submissions sections which all place export/bulk routes before wildcard routes.
```
