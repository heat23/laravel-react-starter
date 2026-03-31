# SEC-004: Add Form Request to resume endpoint

## Problem
`SubscriptionController::resume()` at line 240 uses a bare `Request`. Other billing endpoints use dedicated Form Requests.

## Fix
Create `ResumeSubscriptionRequest`.

## Prompt
```
/v Create app/Http/Requests/Billing/ResumeSubscriptionRequest.php with authorize() returning true if user is authenticated. No validation rules needed. Use it in SubscriptionController::resume() replacing the bare Request parameter. Follow the pattern of CancelSubscriptionRequest.
```
