# DATA-001: Add transaction wrapper to toggleActive

## Problem
`AdminUsersController::toggleActive()` at line 280 performs soft-delete/restore + audit log without a DB transaction. `bulkDeactivate()` and `bulkRestore()` correctly use transactions.

## Fix
Wrap the delete/restore + audit log operations in `DB::transaction()`.

## Prompt
```
/v In app/Http/Controllers/Admin/AdminUsersController.php, wrap the toggleActive() method's delete/restore + audit log operations (lines 289-307) in a DB::transaction() closure, matching the pattern used by bulkDeactivate() and bulkRestore(). Keep cache invalidation outside the transaction since it's a side effect.
```
