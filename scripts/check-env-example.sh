#!/usr/bin/env bash
# check-env-example.sh
#
# Diffs env() keys referenced in config/ against .env.example.
# Exits non-zero and lists any project-specific keys that are undocumented.
#
# Usage:
#   bash scripts/check-env-example.sh
#   bash scripts/check-env-example.sh --strict   # fail even on commented-out keys

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_EXAMPLE="$REPO_ROOT/.env.example"
CONFIG_DIR="$REPO_ROOT/config"
STRICT="${1:-}"

if [[ ! -f "$ENV_EXAMPLE" ]]; then
  echo "ERROR: .env.example not found at $ENV_EXAMPLE" >&2
  exit 1
fi

# ── Internal-only keys ────────────────────────────────────────────────────────
# These have sensible code defaults and are not deployment-relevant.
# A developer cloning the repo does not need to know about them.
INTERNAL_KEYS=(
  ACTIVITY_TRACKING_WINDOW
  APP_CHANGELOG_ITEM
  APP_FAKER_LOCALE
  APP_FALLBACK_LOCALE
  APP_LOCALE
  APP_MAINTENANCE_DRIVER
  APP_MAINTENANCE_STORE
  APP_PREVIOUS_KEYS
  APP_TIMEZONE
  AUTH_GUARD
  AUTH_MODEL
  AUTH_PASSWORD_BROKER
  AUTH_PASSWORD_RESET_TOKEN_TABLE
  AUTH_PASSWORD_TIMEOUT
  BILLING_LEGACY_SUBSCRIBE
  BILLING_LOCK_TIMEOUT
  BROADCAST_DRIVER
  CACHE_PREFIX
  CASHIER_CURRENCY
  CASHIER_CURRENCY_LOCALE
  CASHIER_INVOICE_RENDERER
  CASHIER_LOGGER
  CASHIER_PAPER
  CASHIER_PATH
  CASHIER_PAYMENT_NOTIFICATION
  CASHIER_REMOTE_ENABLED
  DB_CACHE_CONNECTION
  DB_CACHE_LOCK_CONNECTION
  DB_CACHE_LOCK_TABLE
  DB_CACHE_TABLE
  DB_CHARSET
  DB_COLLATION
  DB_FOREIGN_KEYS
  DB_QUEUE
  DB_QUEUE_CONNECTION
  DB_QUEUE_RETRY_AFTER
  DB_QUEUE_TABLE
  DB_SOCKET
  DB_URL
  FILESYSTEM_DISK
  HORIZON_DOMAIN
  HORIZON_PATH
  LOG_DAILY_DAYS
  LOG_DEPRECATIONS_TRACE
  LOG_PAPERTRAIL_HANDLER
  LOG_SLACK_EMOJI
  LOG_SLACK_USERNAME
  LOG_SLACK_WEBHOOK_URL
  LOG_STACK
  LOG_STDERR_FORMATTER
  LOG_SYSLOG_FACILITY
  MAIL_EHLO_DOMAIN
  MAIL_LOG_CHANNEL
  MAIL_SENDMAIL_PATH
  MAIL_URL
  QUEUE_FAILED_DRIVER
  SANCTUM_STATEFUL_DOMAINS
  SANCTUM_TOKEN_PREFIX
  SENTRY_CONTROLLERS_BASE_NAMESPACE
  SENTRY_ENVIRONMENT
  SENTRY_RELEASE
  SENTRY_SEND_DEFAULT_PII
  SESSION_CONNECTION
  SESSION_DOMAIN
  SESSION_EXPIRE_ON_CLOSE
  SESSION_HTTP_ONLY
  SESSION_PARTITIONED_COOKIE
  SESSION_PATH
  SESSION_SAME_SITE
  SESSION_SECURE_COOKIE
  SESSION_STORE
  SESSION_TABLE
  STRIPE_WEBHOOK_TOLERANCE
)

# ── Framework-infrastructure prefixes to skip ─────────────────────────────────
SKIP_PREFIXES=(
  AWS_
  BEANSTALKD_
  DYNAMODB_
  MEMCACHED_
  MYSQL_ATTR_
  PAPERTRAIL_
  POSTMARK_
  REDIS_
  RESEND_
  SLACK_BOT_
  SQS_
  VITE_SENTRY_
)

# ── Build lookup: keys present in .env.example ────────────────────────────────
declare -A EXAMPLE_KEYS_ACTIVE
declare -A EXAMPLE_KEYS_COMMENTED

while IFS= read -r line; do
  if [[ "$line" =~ ^[[:space:]]*([A-Z][A-Z0-9_]+)= ]]; then
    EXAMPLE_KEYS_ACTIVE["${BASH_REMATCH[1]}"]=1
  elif [[ "$line" =~ ^[[:space:]]*#[[:space:]]*([A-Z][A-Z0-9_]+)= ]]; then
    EXAMPLE_KEYS_COMMENTED["${BASH_REMATCH[1]}"]=1
  fi
done < "$ENV_EXAMPLE"

# ── Build skip set ────────────────────────────────────────────────────────────
declare -A SKIP_KEYS
for key in "${INTERNAL_KEYS[@]}"; do
  SKIP_KEYS["$key"]=1
done

skip_by_prefix() {
  local k="$1"
  for prefix in "${SKIP_PREFIXES[@]}"; do
    [[ "$k" == ${prefix}* ]] && return 0
  done
  return 1
}

# ── Extract env() keys from config/ ──────────────────────────────────────────
MISSING_ACTIVE=()
MISSING_COMMENTED=()

while IFS= read -r key; do
  [[ -z "$key" ]] && continue
  [[ -n "${SKIP_KEYS[$key]+x}" ]] && continue
  skip_by_prefix "$key" && continue

  if [[ -n "${EXAMPLE_KEYS_ACTIVE[$key]+x}" ]]; then
    continue
  elif [[ -n "${EXAMPLE_KEYS_COMMENTED[$key]+x}" ]]; then
    MISSING_COMMENTED+=("$key")
  else
    MISSING_ACTIVE+=("$key")
  fi
done < <(
  grep -rh "env('" "$CONFIG_DIR" \
    | grep -oE "env\('[A-Z][A-Z0-9_]+'" \
    | sed "s/^env('//; s/'$//" \
    | sort -u
)

# ── Report ────────────────────────────────────────────────────────────────────
EXIT=0

if [[ ${#MISSING_ACTIVE[@]} -gt 0 ]]; then
  echo "✗ ${#MISSING_ACTIVE[@]} key(s) referenced in config/ but ABSENT from .env.example:"
  for key in "${MISSING_ACTIVE[@]}"; do
    echo "  - $key"
  done
  EXIT=1
fi

if [[ ${#MISSING_COMMENTED[@]} -gt 0 ]] && [[ "$STRICT" == "--strict" ]]; then
  echo "✗ ${#MISSING_COMMENTED[@]} key(s) only appear as comments in .env.example (use --strict to fail):"
  for key in "${MISSING_COMMENTED[@]}"; do
    echo "  - $key"
  done
  EXIT=1
elif [[ ${#MISSING_COMMENTED[@]} -gt 0 ]]; then
  echo "  (${#MISSING_COMMENTED[@]} key(s) documented as comments only — run with --strict to flag)"
fi

if [[ $EXIT -eq 0 ]]; then
  echo "✓ .env.example covers all project env() keys."
fi

exit $EXIT
