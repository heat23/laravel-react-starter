# Production Monitoring

## Uptime Monitor Decision Matrix

| Criteria | Uptime Kuma | Better Stack |
|----------|-------------|--------------|
| Cost | $0 (self-hosted VPS) | $25/mo Basics |
| Setup time | ~20 min | ~5 min |
| Alerting | Email, Telegram, Slack, SMS (via webhook) | Email, SMS, PagerDuty, Slack |
| On-call scheduling | ❌ | ✅ |
| Status page | ✅ (built-in) | ✅ (hosted) |
| Log retention | 90 days (configurable) | 90 days |
| Multi-location checks | ❌ (single VPS location) | ✅ (8 global locations) |
| Ideal for | Solo operator, cost-sensitive | Team, SLA requirements |

**Recommendation:** Start with Uptime Kuma on the same VPS (zero cost, no vendor dependency).
Switch to Better Stack when you need multi-location checks or on-call scheduling for a team.

---

## Uptime Kuma Setup (Self-Hosted, $0)

### 1. Install on VPS

```bash
docker pull louislam/uptime-kuma:1
docker run -d \
  --restart=always \
  -p 3001:3001 \
  -v uptime-kuma:/app/data \
  --name uptime-kuma \
  louislam/uptime-kuma:1
```

Navigate to `http://your-vps-ip:3001` and create an admin account on first run.
Optionally put Nginx in front (see `nginx-gzip.conf` for a reference config).

### 2. Add a Monitor for `/health`

In Uptime Kuma → **Add New Monitor**:

| Field | Value |
|-------|-------|
| Monitor Type | `HTTP(s)` |
| Friendly Name | `App Health` |
| URL | `https://your-domain.com/health` |
| Heartbeat Interval | `60` seconds |
| Retries | `3` |
| HTTP Method | `GET` |
| Expected Status Code | `200` |

**Headers tab — add one header:**

```
Name:  Authorization
Value: Bearer <paste your HEALTH_CHECK_TOKEN value here>
```

**Advanced → Body keyword (optional):** Set to `healthy` to alert if the app returns 200 but
`status` is `degraded`. Leave blank to only alert on HTTP failure.

---

## Better Stack Setup (Paid Alternative)

1. Log in → **Monitors → New monitor**
2. URL: `https://your-domain.com/health`
3. Interval: 60 seconds
4. Regions: select 2–3 globally spread locations
5. **Custom headers:** `Authorization: Bearer <HEALTH_CHECK_TOKEN value>`
6. **Expected status:** 200
7. **Keyword check:** `"status":"healthy"` (strict — also alerts on degraded)
8. Wire on-call schedule to your phone number for SMS pages

---

## /health Endpoint Reference

### Authentication

The endpoint supports three auth modes, evaluated in priority order:

1. **Bearer token** (recommended for external monitors):

   Send header `Authorization` with value `Bearer <your token>`.
   Configure token via `HEALTH_CHECK_TOKEN` env var.

2. **IP allowlist** (for internal monitors with no token):

   ```env
   HEALTH_CHECK_IPS=10.0.0.1,10.0.0.2
   ```

3. **Local-only fallback**: if neither token nor IPs are configured,
   access is allowed only when `APP_ENV=local`.

When a token is configured, the IP allowlist is ignored entirely.

### Response Shape

**Healthy (HTTP 200):**
```json
{
  "status": "healthy",
  "checks": {
    "database": { "status": "ok", "message": "Connection successful", "response_time_ms": 4.2 },
    "cache":    { "status": "ok", "message": "Read/write successful", "response_time_ms": 1.1 },
    "queue":    { "status": "ok", "message": "Queue nominal",         "response_time_ms": 0.8 },
    "disk":     { "status": "ok", "message": "Disk usage nominal",    "response_time_ms": 0.3 }
  },
  "timestamp": "2026-04-23T14:00:00.000Z"
}
```

**Degraded (HTTP 200, `status` = `degraded`):** One or more checks returned `warning`.
App is serving traffic but a subsystem is under stress.

**Unhealthy (HTTP 503, `status` = `unhealthy`):** One or more checks returned `error`.
Database or cache unreachable — likely a hard outage.

**Unauthorized (HTTP 403):** Token missing or wrong. Verify `HEALTH_CHECK_TOKEN` in `.env`.

Individual check statuses: `ok` | `warning` | `error`. Overall `status` reflects the worst check.

### Configurable Thresholds

```env
# .env (production)
HEALTH_CHECK_TOKEN=              # Required for external monitors; rotate quarterly
HEALTH_CHECK_IPS=127.0.0.1      # Comma-separated allowlist (ignored when token set)
DISK_USAGE_WARNING_PERCENT=80   # Default 80
DISK_USAGE_CRITICAL_PERCENT=95  # Default 95
QUEUE_WARNING_THRESHOLD=1000    # Jobs in queue before warning
```

---

## Token Rotation Runbook

Rotate `HEALTH_CHECK_TOKEN` whenever:
- A team member with server access leaves
- The token may have been exposed (logs, error messages, git history)
- Quarterly rotation (recommended)

**Steps:**

1. Generate a new token:
   ```bash
   php artisan tinker --execute="echo bin2hex(random_bytes(32));"
   ```

2. Update `.env` on the server:
   ```bash
   # Edit /var/www/your-app/.env
   # Set HEALTH_CHECK_TOKEN to the new value
   php artisan config:clear
   ```

3. Update the monitor in Uptime Kuma / Better Stack with the new token value.

4. Verify the endpoint responds correctly:
   ```bash
   # TOKEN is the new value from step 1
   curl -H "Authorization: Bearer $TOKEN" https://your-domain.com/health
   ```

5. Revoke the old token from any other systems that used it.

---

## Alert Routing

### Email (Uptime Kuma)

Settings → Notifications → Add → **Email (SMTP)**.
Use your existing `MAIL_HOST` / `MAIL_MAILER` configuration from `.env`.

### SMS (Better Stack)

Better Stack Basics includes SMS alerts. Configure your phone under
**On-call → Escalation policies**.

For Uptime Kuma SMS: use a **Webhook** notification pointing to Twilio, Vonage,
or a Telegram bot (all have free tiers).

### Slack / PagerDuty

Both tools support Slack webhooks natively.
- Uptime Kuma: Settings → Notifications → Slack
- Better Stack: Integrations → Slack

---

## Recommended Alert Rules

| Condition | Threshold | Action |
|-----------|-----------|--------|
| HTTP non-200 | 3 consecutive failures | Email + SMS |
| HTTP 503 | 1 failure | Immediate SMS (database/cache down = outage) |
| `status` = `degraded` keyword miss | 3 failures | Email only |
| Response time > 5 s | 3 consecutive | Email (investigate) |

Set **retries to 3** with a **30 s retry interval** before alerting to filter transient blips.
