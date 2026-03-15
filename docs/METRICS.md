# Product Metrics Framework

Defines the metrics this starter template tracks out of the box, how they're calculated, and where to find them.

## North Star Metric

**Weekly Active Users (WAU)** — unique users who performed at least one authenticated action in the last 7 days.

**Why WAU:** For most SaaS products, weekly activity correlates with retention and expansion revenue better than daily (too noisy) or monthly (too slow to react). WAU strikes a balance between actionable signal and noise reduction.

**Calculation:**
```sql
SELECT COUNT(DISTINCT id) FROM users
WHERE last_active_at >= NOW() - INTERVAL 7 DAY
  AND deleted_at IS NULL;
```

**Where surfaced:** Admin dashboard (after implementing `last_active_at` tracking).

## Input Metrics

These metrics feed the north star. Improving any input metric should improve WAU.

### Signup Rate

New user registrations per day/week.

```sql
SELECT DATE(created_at) as date, COUNT(*) as signups
FROM users
WHERE created_at >= NOW() - INTERVAL 30 DAY
GROUP BY DATE(created_at);
```

### Activation Rate

Percentage of users who complete verification AND perform a meaningful action within 7 days of signup.

**Definition of "meaningful action":** Customize per product. Default: email verified + at least one settings change OR API token created OR billing page visited.

```sql
-- Verified within 7 days
SELECT
  COUNT(CASE WHEN email_verified_at IS NOT NULL
    AND email_verified_at <= created_at + INTERVAL 7 DAY THEN 1 END) * 100.0 / COUNT(*)
  as activation_rate
FROM users
WHERE created_at >= NOW() - INTERVAL 30 DAY;
```

### Trial-to-Paid Conversion Rate

Percentage of trial users who subscribe to a paid plan.

```sql
SELECT
  COUNT(DISTINCT s.user_id) * 100.0 / COUNT(DISTINCT u.id) as conversion_rate
FROM users u
LEFT JOIN subscriptions s ON s.user_id = u.id AND s.stripe_status = 'active'
WHERE u.trial_ends_at IS NOT NULL;
```

## Health Metrics

These indicate individual user health and predict churn.

### Customer Health Score (0-100)

Composite score based on 4 dimensions:

| Dimension | Weight | Score Range | Calculation |
|-----------|--------|-------------|-------------|
| Login frequency (30 days) | 25 | 0-25 | 0 logins = 0, 1-3 = 10, 4-10 = 18, 11+ = 25 |
| Feature adoption breadth | 25 | 0-25 | Count of distinct feature categories used |
| Billing status | 25 | 0-25 | active = 25, trialing = 20, past_due = 5, canceled/none = 0 |
| Profile completion | 25 | 0-25 | verified email = 10, settings configured = 8, password set = 7 |

**Implementation:** `CustomerHealthService::calculateHealthScore(User $user)`

**Health brackets:**
- 76-100: Healthy (green) — engaged, paying, using features
- 51-75: Moderate (yellow) — some engagement gaps
- 26-50: At risk (orange) — declining usage or billing issues
- 0-25: Critical (red) — churning or already gone

### Churn Rate

Monthly churn = canceled subscriptions in period / active subscriptions at start of period.

Already calculated in `AdminBillingStatsService::getDashboardStats()` as `churn_rate`.

## Where Metrics Are Surfaced

| Metric | Location | Update Frequency |
|--------|----------|-----------------|
| WAU | Admin dashboard | Cached, 5-min TTL |
| Signup rate | Admin dashboard | Cached, 5-min TTL |
| Activation rate | Admin billing dashboard | Cached, 5-min TTL |
| Trial conversion | Admin billing dashboard | Cached, 5-min TTL |
| Health score | User dashboard (own score) | Real-time |
| Health distribution | Admin dashboard | Cached, 5-min TTL |
| Churn rate | Admin billing dashboard | Cached, 5-min TTL |
| MRR | Admin billing dashboard | Cached, 5-min TTL |

## Adding Custom Metrics

To track a new metric:

1. Identify the data source (existing table or new column needed)
2. Add calculation method to the appropriate service (`CustomerHealthService` for user-level, `AdminBillingStatsService` for admin-level)
3. Cache with `AdminCacheKey` if it's an aggregate (5-min TTL default)
4. Add to the relevant dashboard (user or admin)
5. Document in this file

## Data Sources

All metrics are derived from existing tables:

| Table | Metrics It Feeds |
|-------|-----------------|
| `users` | Signup rate, activation rate, WAU, health score |
| `subscriptions` | Trial conversion, churn rate, MRR, billing health |
| `audit_logs` | Feature adoption (via action types), login frequency |
| `user_settings` | Profile completion score |
| `personal_access_tokens` | Feature adoption (API usage) |
| `notifications` | Email engagement tracking |
