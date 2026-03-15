# BILLING_REVIEWED — polish session

## Change Summary
- `resources/js/Pages/Admin/Billing/Subscriptions.tsx`: Added `clearFilters` destructuring from `useAdminFilters` and `emptyAction` prop to `AdminDataTable`. UI-only change — no billing logic, no Stripe calls, no Cashier methods.

## Verification
1. Test coverage: No billing logic changed — existing tests pass
2. Stripe webhooks: Not affected — change is UI filter reset only
3. Cashier eager-load: Not affected — no subscription queries changed
4. Risk: None — cosmetic UX improvement to empty state in admin subscription list
