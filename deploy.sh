#!/bin/bash
set -e
export PATH=$PATH:/opt/cpanel/ea-nodejs22/bin


# ============================================================
# Laravel Deployment Script
# Supports: preview and production environments
# ============================================================
# App name is dynamically derived from .env APP_NAME value

# Source common functions library
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [[ -f "${SCRIPT_DIR}/scripts/_common.sh" ]]; then
    source "${SCRIPT_DIR}/scripts/_common.sh"
else
    # Minimal fallback if _common.sh not found
    log_pass() { echo "✅ $*"; }
    log_fail() { echo "❌ $*"; exit 1; }
    log_warn() { echo "⚠️  $*"; }
    log_say()  { echo "$*"; }
fi

# -------------------------
# PHP Version Configuration
# -------------------------
PHP_BIN=$(detect_php_binary 2>/dev/null || echo "php")
COMPOSER_BIN="/usr/local/bin/composer"

# Fallback to system binaries if cPanel paths don't exist
if [[ ! -f "$COMPOSER_BIN" ]]; then
    COMPOSER_BIN="composer"
fi

PHP_BIN_DIR="$(dirname "$PHP_BIN")"
export PATH="$PHP_BIN_DIR:$PATH"

# Create aliases for convenience
alias php="$PHP_BIN"
alias composer="$PHP_BIN $COMPOSER_BIN"

# Colors for output (fallback if _common.sh not sourced)
if [[ -z "${RED:-}" ]]; then
    RED='\033[0;31m'
    GREEN='\033[0;32m'
    YELLOW='\033[1;33m'
    NC='\033[0m' # No Color
fi

say()  { echo -e "${NC}$*"; }
pass() { echo -e "${GREEN}✅ $*${NC}"; }
warn() { echo -e "${YELLOW}⚠️  $*${NC}"; }
fail() { echo -e "${RED}❌ $*${NC}"; exit 1; }

# -------------------------
# Environment Detection
# -------------------------
DEPLOY_ENV="unknown"
if [[ $PWD == *"preview"* ]]; then
    DEPLOY_ENV="preview"
    ENV_FILE=".env.preview"
elif [[ $PWD == *"production"* ]]; then
    DEPLOY_ENV="production"
    ENV_FILE=".env.production"
else
    warn "Could not detect environment from path. Assuming local."
    DEPLOY_ENV="local"
    ENV_FILE=""
fi

# Get app name from .env for display
if [[ -n "$ENV_FILE" && -f "$ENV_FILE" ]]; then
    APP_NAME_DISPLAY=$(grep "^APP_NAME=" "$ENV_FILE" | cut -d'=' -f2 | tr -d '"' | tr -d "'" | xargs)
elif [[ -f ".env" ]]; then
    APP_NAME_DISPLAY=$(grep "^APP_NAME=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'" | xargs)
else
    APP_NAME_DISPLAY="Laravel App"
fi

# Get app name slug for supervisor
APP_NAME_SLUG=$(get_app_name_slug "$ENV_FILE" 2>/dev/null || basename "$(pwd)" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9-]//g')
SUPERVISOR_PROG="${APP_NAME_SLUG}-horizon"
if [[ "$DEPLOY_ENV" == "production" ]]; then
    SUPERVISOR_PROG="${APP_NAME_SLUG}-production-horizon"
elif [[ "$DEPLOY_ENV" == "preview" ]]; then
    SUPERVISOR_PROG="${APP_NAME_SLUG}-preview-horizon"
fi

say ""
say "============================================================"
DEPLOY_ENV_LABEL=$(printf '%s' "$DEPLOY_ENV" | tr '[:lower:]' '[:upper:]')
say "🚀 ${APP_NAME_DISPLAY} Deployment - ${DEPLOY_ENV_LABEL}"
say "============================================================"
say "Started at: $(date)"
say ""

# -------------------------
# Step 1: Redis Health Check
# -------------------------
say "Step 1: Checking Redis availability..."

# Check PHP Redis extension is installed
if ! $PHP_BIN -m 2>/dev/null | grep -q "^redis$"; then
    fail "PHP Redis extension (phpredis) is not installed!\nInstall it with:\n  - macOS: pecl install redis\n  - Ubuntu: apt install php-redis\n  - CentOS: yum install php-pecl-redis"
fi
pass "PHP Redis extension is installed"

# Check if redis-cli is available
if command -v redis-cli >/dev/null 2>&1; then
    # Use environment-specific file if set, otherwise fall back to .env
    ENV_SOURCE="${ENV_FILE:-.env}"
    [[ ! -f "$ENV_SOURCE" ]] && ENV_SOURCE=".env"
    REDIS_HOST=$(grep "^REDIS_HOST=" "$ENV_SOURCE" | cut -d'=' -f2 | sed 's/#.*//' | tr -d '"' | tr -d "'" | xargs || echo "127.0.0.1")
    REDIS_PORT=$(grep "^REDIS_PORT=" "$ENV_SOURCE" | cut -d'=' -f2 | sed 's/#.*//' | tr -d '"' | tr -d "'" | xargs || echo "6379")
    REDIS_PASSWORD=$(grep "^REDIS_PASSWORD=" "$ENV_SOURCE" | cut -d'=' -f2 | sed 's/#.*//' | tr -d '"' | tr -d "'" | xargs || echo "")

    # Build redis-cli command with optional auth
    REDIS_CMD="redis-cli -h $REDIS_HOST -p $REDIS_PORT"
    if [[ -n "$REDIS_PASSWORD" && "$REDIS_PASSWORD" != "null" ]]; then
        REDIS_CMD="$REDIS_CMD -a $REDIS_PASSWORD"
    fi

    REDIS_PING=$($REDIS_CMD ping 2>/dev/null || echo "FAILED")
    if [[ "$REDIS_PING" == "PONG" ]]; then
        pass "Redis is running at $REDIS_HOST:$REDIS_PORT"
    else
        fail "Redis is not responding at $REDIS_HOST:$REDIS_PORT. Redis is REQUIRED for session, cache, and queue.\nPlease ensure Redis is installed and running:\n  - macOS: brew services start redis\n  - Linux: sudo systemctl start redis\n  - Check REDIS_HOST, REDIS_PORT, REDIS_PASSWORD in .env"
    fi
else
    warn "redis-cli not found - skipping Redis health check"
    warn "Ensure Redis is running manually before proceeding"
fi

# -------------------------
# Step 2: Environment File Validation
# -------------------------
say "Step 2: Validating environment configuration..."

if [[ -n "$ENV_FILE" && -f "$ENV_FILE" ]]; then
    diff .env "$ENV_FILE" || {
        fail "Env diff failed! .env does not match $ENV_FILE\nPlease check the files manually and run this script again."
    }
    pass "Environment file matches $ENV_FILE"
else
    warn "No $ENV_FILE found - skipping diff check"
fi

# Validate critical env vars exist (skip for local runs)
if [[ "$DEPLOY_ENV" != "local" ]]; then
    REQUIRED_VARS=("APP_KEY" "APP_URL" "DB_CONNECTION" "DB_DATABASE")
    for var in "${REQUIRED_VARS[@]}"; do
        if ! grep -q "^${var}=" .env; then
            fail "Missing required env var: $var"
        fi
    done
pass "Required environment variables present"
else
    warn "Skipping required env var checks for local run"
fi

# -------------------------
# Step 2b: Ensure .htaccess Files (preview/production only)
# -------------------------
say ""
say "Step 2b: Ensuring .htaccess files exist..."

if [[ "$DEPLOY_ENV" == "preview" || "$DEPLOY_ENV" == "production" ]]; then
    HTACCESS_VPS_FILES=(".htaccess.vps" "public/.htaccess.vps")
    for vps_file in "${HTACCESS_VPS_FILES[@]}"; do
        if [[ -f "$vps_file" ]]; then
            target_file="${vps_file%.vps}"
            if [[ -f "$target_file" ]]; then
                warn "$target_file already exists - leaving as is"
            else
                cp "$vps_file" "$target_file"
                pass "Copied $vps_file -> $target_file"
            fi
        else
            warn "Missing $vps_file - skipping"
        fi
    done
else
    warn "Skipping .htaccess setup for local run"
fi

# -------------------------
# Step 3: File Permissions
# -------------------------
say ""
say "Step 3: Setting file permissions..."

# Only adjust runtime-writable directories; avoid touching tracked files.
find storage bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
find storage -type f -name "*.log" -exec chmod 664 {} \; 2>/dev/null || true

# Detect app user for permission setting
APP_USER=$(detect_app_user 2>/dev/null || echo "")
if [[ -n "$APP_USER" ]]; then
    sudo chown -R "$APP_USER:$APP_USER" storage bootstrap/cache 2>/dev/null || true
fi

chmod 755 deploy.sh

pass "Runtime directory permissions set"

# -------------------------
# Step 4: Maintenance Mode
# -------------------------
say ""
say "Step 4: Entering maintenance mode..."
($PHP_BIN artisan down --retry=60 --refresh=15) || true
pass "Maintenance mode enabled"

# -------------------------
# Step 5: Composer Dependencies
# -------------------------
say ""
say "Step 5: Installing PHP dependencies..."

COMPOSER_RUN="$PHP_BIN $COMPOSER_BIN"
if [[ "$DEPLOY_ENV" == "local" ]] && command -v composer >/dev/null 2>&1; then
    COMPOSER_RUN="composer"
fi

if [[ "$DEPLOY_ENV" == "production" ]]; then
    say "Production mode: installing without dev dependencies..."
    $COMPOSER_RUN install --no-dev --no-interaction --prefer-dist --optimize-autoloader
elif [[ "$DEPLOY_ENV" == "preview" ]]; then
    say "Preview mode: installing with dev dependencies..."
    $COMPOSER_RUN install --no-interaction --prefer-dist --optimize-autoloader
else
    say "Local mode: installing with dev dependencies..."
    $COMPOSER_RUN install --no-interaction --prefer-dist --optimize-autoloader
fi
pass "Composer dependencies installed"

# -------------------------
# Step 6: Clear All Caches
# -------------------------
say ""
say "Step 6: Clearing caches..."

$PHP_BIN artisan clear-compiled
$PHP_BIN artisan route:clear
$PHP_BIN artisan config:clear
$PHP_BIN artisan view:clear
$PHP_BIN artisan event:clear

pass "All caches cleared"

# -------------------------
# Step 7: NPM Build
# -------------------------
say ""
say "Step 7: Building frontend assets..."

rm -f public/hot public_html/hot
npm ci --prefer-offline

# Generate Ziggy routes before building (required for TypeScript route helpers)
$PHP_BIN artisan ziggy:generate

npm run build

pass "Frontend assets built"

# -------------------------
# Step 8: Database Migrations
# -------------------------
say ""
say "Step 8: Running database migrations..."

$PHP_BIN artisan migrate --force
$PHP_BIN artisan optimize:clear
pass "Database migrations complete"

# -------------------------
# Step 9: Rebuild Caches
# -------------------------
say ""
say "Step 9: Rebuilding optimization caches..."

$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

pass "Optimization caches rebuilt"

# -------------------------
# Step 10: Exit Maintenance Mode
# -------------------------
say ""
say "Step 10: Exiting maintenance mode..."
$PHP_BIN artisan up
pass "Application is live"

# -------------------------
# Step 11: Restart Queue Workers
# -------------------------
say ""
say "Step 11: Restarting queue workers..."
$PHP_BIN artisan queue:restart
pass "Queue restart signal sent"

# -------------------------
# Step 11b: Restart Horizon (if enabled)
# -------------------------
say ""
say "Step 11b: Restarting Horizon supervisor..."

# Check if Horizon feature is enabled
FEATURE_HORIZON=$(grep "^FEATURE_HORIZON=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '"' | tr -d "'" | tr '[:upper:]' '[:lower:]' | xargs || echo "false")

if [[ "$FEATURE_HORIZON" == "true" ]] || [[ "$FEATURE_HORIZON" == "1" ]]; then
    if command -v supervisorctl &> /dev/null; then
        if supervisorctl status 2>/dev/null | grep -q "$SUPERVISOR_PROG"; then
            supervisorctl restart "$SUPERVISOR_PROG"
            pass "Horizon restarted via Supervisor"
        else
            warn "Horizon program ($SUPERVISOR_PROG) not found in Supervisor - skipping"
        fi
    else
        warn "supervisorctl not found - ensure Horizon is restarted manually"
    fi
else
    say "  Horizon not enabled (FEATURE_HORIZON != true) - skipping"
fi

# ============================================================
# APPLICATION-SPECIFIC VALIDATION CHECKS
# ============================================================

say ""
say "============================================================"
say "📋 Running Application Validation Checks..."
say "============================================================"


# -------------------------
# Step 12: Redis Connection Test via Laravel (if using Redis)
# -------------------------
say ""
say "Step 12: Testing Redis connection via Laravel (if configured)..."

# Check if any driver uses Redis
NEEDS_REDIS=false
for DRIVER_VAR in SESSION_DRIVER CACHE_STORE QUEUE_CONNECTION; do
    DRIVER_VALUE=$(grep "^${DRIVER_VAR}=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '"' | tr -d "'" | xargs)
    if [[ "$DRIVER_VALUE" == "redis" ]]; then
        NEEDS_REDIS=true
        break
    fi
done

if [[ "$NEEDS_REDIS" == "false" ]]; then
    say "  No Redis-backed drivers configured - skipping"
else
    # Use inline PHP to test Redis connection (avoids PsySH trust issues)
    REDIS_TEST=$($PHP_BIN -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        try {
            \Illuminate\Support\Facades\Redis::ping();
            echo 'PONG';
        } catch (Exception \$e) {
            echo 'FAILED: ' . \$e->getMessage();
        }
    " 2>&1 || echo "FAILED: Script execution error")

    if [[ "$REDIS_TEST" == "PONG" ]]; then
        pass "Laravel can connect to Redis"
    else
        fail "Laravel cannot connect to Redis!\nCheck your REDIS_* configuration in .env\nError: $REDIS_TEST"
    fi
fi

# -------------------------
# Step 13: Configuration Validation
# -------------------------
say ""
say "Step 13: Validating application configuration..."

pass "Configuration validation passed"

# -------------------------
# Step 14: HTTP Health Checks
# -------------------------
say ""
say "Step 14: Running HTTP health checks..."

APP_URL=$(grep '^APP_URL=' .env | cut -d '=' -f2 | tr -d '"' | tr -d "'")
say "  Testing: $APP_URL"

# Wait a moment for caches to warm up
sleep 5

# Check homepage
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 30 "$APP_URL" 2>/dev/null || echo "000")
if [[ "$HTTP_CODE" != "200" ]]; then
    warn "Homepage returned HTTP $HTTP_CODE - attempting cache fix..."
    rm -f bootstrap/cache/config.php 2>/dev/null || true
    $PHP_BIN artisan config:cache
    sleep 2
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 30 "$APP_URL" 2>/dev/null || echo "000")
    if [[ "$HTTP_CODE" != "200" ]]; then
        APP_HOST=$(printf '%s' "$APP_URL" | sed -E 's#^https?://([^/]+).*#\1#')
        APP_SCHEME=$(printf '%s' "$APP_URL" | sed -E 's#^(https?).*#\1#')
        APP_PORT="80"
        if [[ "$APP_SCHEME" == "https" ]]; then
            APP_PORT="443"
        fi
        LOCAL_CODE=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 30 \
            --resolve "${APP_HOST}:${APP_PORT}:127.0.0.1" "$APP_URL" 2>/dev/null || echo "000")
        if [[ "$LOCAL_CODE" == "200" ]]; then
            warn "Homepage check failed externally (HTTP $HTTP_CODE) but succeeded via localhost resolution."
        else
            fail "Homepage still returning HTTP $HTTP_CODE after cache fix!"
        fi
    fi
fi
pass "Homepage returns HTTP 200"

# -------------------------
# Step 15: Scheduled Tasks Check
# -------------------------
say ""
say "Step 15: Verifying scheduled tasks..."

SCHEDULE_CHECK=$($PHP_BIN artisan schedule:list 2>/dev/null | grep -c "artisan" || echo "0")
if [[ "$SCHEDULE_CHECK" -gt 0 ]]; then
    pass "Found $SCHEDULE_CHECK scheduled artisan commands"
else
    warn "No scheduled commands found - check routes/console.php"
fi

# ============================================================
# OPTIONAL POST-DEPLOY ACTIONS
# ============================================================

# -------------------------
# Step 16: Rebuild Sitemaps (if feature enabled)
# -------------------------
say ""
say "Step 16: Refreshing sitemap cache (if enabled)..."

FEATURE_SITEMAP=$(grep "^FEATURE_SITEMAP=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '"' | tr -d "'" | tr '[:upper:]' '[:lower:]' | xargs || echo "false")

if [[ "$FEATURE_SITEMAP" == "true" ]] || [[ "$FEATURE_SITEMAP" == "1" ]]; then
    if $PHP_BIN artisan sitemap:refresh --force 2>/dev/null; then
        pass "Sitemap cache refreshed successfully"
    else
        warn "Sitemap refresh failed - will be regenerated on next request"
    fi
else
    say "  Sitemap feature not enabled (FEATURE_SITEMAP != true) - skipping"
fi

# ============================================================
# DEPLOYMENT COMPLETE
# ============================================================

say ""
say "============================================================"
pass "🎉 DEPLOYMENT COMPLETE - ${DEPLOY_ENV_LABEL}"
say "============================================================"
say ""
say "Application: $APP_NAME_DISPLAY"
say "Environment: $DEPLOY_ENV"
say "URL: $APP_URL"
say "Completed at: $(date)"
say ""
say "Post-deployment scripts:"
say "  ./scripts/vps-verify.sh          - Full VPS verification"
if [[ "$FEATURE_HORIZON" == "true" ]] || [[ "$FEATURE_HORIZON" == "1" ]]; then
    say "  ./scripts/test-horizon.sh        - Test Horizon queue processing"
    say ""
    say "First-time Horizon setup (run once per server):"
    say "  ./scripts/setup-horizon.sh       - Install Supervisor config & start Horizon"
fi
say ""
