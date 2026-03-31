# AUTH-002: Add password confirmation on destructive billing operations

## Problem
Cancel, swap, and quantity update operations in `SubscriptionController` do not require password confirmation despite being high-impact financial mutations.

## Fix
Add `password` confirmation requirement to destructive billing requests.

## Prompt
```
/v Add password confirmation to destructive billing operations:
1. In CancelSubscriptionRequest, add 'password' => ['required_if:immediately,true', 'current_password'] to rules().
2. In SwapPlanRequest, add 'password' => ['required', 'current_password'] to rules().
3. Update the frontend billing forms (resources/js/Pages/Billing/) to include a password confirmation field when canceling immediately or swapping plans.
```
