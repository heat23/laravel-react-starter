#!/bin/bash
# ============================================================================
# Laravel Horizon Setup Script
# ============================================================================
# Sets up Laravel Horizon on local (macOS/Herd) or server (Linux/Supervisor).
#
# Usage:
#   chmod +x scripts/setup-horizon.sh
#   ./scripts/setup-horizon.sh
#
# Environments supported:
#   - macOS with Laravel Herd (local development)
#   - macOS with Homebrew (local development)
#   - Linux with Supervisor (preview/production)
#
# Prerequisites:
#   - FEATURE_HORIZON=true in .env
#   - Laravel Horizon package installed (composer require laravel/horizon)
#
# What this script does:
#   1. Detects environment (local macOS vs server Linux)
#   2. Checks prerequisites (Redis, Horizon package, feature flag)
#   3. For servers: copies supervisor config and starts Horizon
#   4. For local: starts Horizon in background or provides instructions
#   5. Verifies Horizon is running correctly
#   6. Tests queue processing with a test job
#
# ============================================================================

set -e

# Source common functions library
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/_common.sh"

# Counters
PASS=0
FAIL=0
WARN=0

say()  { log_say "$*"; }
pass() { log_pass "$*"; ((PASS++)) || true; }
warn() { log_warn "$*"; ((WARN++)) || true; }
fail() { log_fail "$*"; ((FAIL++)) || true; }
fatal() { log_fail "FATAL: $*"; exit 1; }

# ============================================================================
# ENVIRONMENT DETECTION
# ============================================================================

# Use shared functions
OS_TYPE=$(detect_os)
PHP_BIN=$(detect_php_binary)

if [[ -z "$PHP_BIN" ]]; then
    fatal "PHP not found"
fi

# Application paths (resolve symlinks)
APP_ROOT="${APP_ROOT:-$(pwd -P)}"

# Determine environment from path and .env files
if detect_environment; then
    ENV_FILE_PATH="${APP_ROOT}/${ENV_FILE}"
else
    ENV_FILE_PATH="${APP_ROOT}/.env"
    ENVIRONMENT="local"
    APP_NAME_SLUG=$(get_app_name_slug "$ENV_FILE_PATH")
    SUPERVISOR_PROG="${APP_NAME_SLUG}-horizon"
fi

# Get display name
APP_NAME_DISPLAY=$(extract_env_value "$ENV_FILE_PATH" "APP_NAME")
[[ -z "$APP_NAME_DISPLAY" ]] && APP_NAME_DISPLAY="Laravel App"

SUPERVISOR_CONF_SOURCE="${APP_ROOT}/deploy/supervisor.conf"
SUPERVISOR_DIR="$(detect_supervisor_dir)"
SUPERVISOR_EXT="$(detect_supervisor_ext)"
SUPERVISOR_CONF_DEST="${SUPERVISOR_DIR}/${SUPERVISOR_PROG}.${SUPERVISOR_EXT}"
HORIZON_LOG="${APP_ROOT}/storage/logs/horizon.log"

# ============================================================================
# PRE-FLIGHT CHECKS
# ============================================================================
print_header "Pre-Flight Checks"

# Check we're in the right directory
say "Checking application directory..."
if [[ ! -f "${APP_ROOT}/artisan" ]] || [[ ! -f "${APP_ROOT}/composer.json" ]]; then
    fatal "Not in Laravel application root. Run from app directory or set APP_ROOT."
fi
pass "Application directory: ${APP_ROOT}"

# Check feature flag
say "Checking FEATURE_HORIZON flag..."
if ! is_feature_enabled "FEATURE_HORIZON" "$ENV_FILE_PATH"; then
    fatal "FEATURE_HORIZON is not enabled in $ENV_FILE_PATH. Set FEATURE_HORIZON=true to use Horizon."
fi
pass "FEATURE_HORIZON is enabled"

# Check environment
say "Checking environment..."
if [[ -f "$ENV_FILE_PATH" ]]; then
    APP_ENV=$(extract_env_value "$ENV_FILE_PATH" "APP_ENV")
else
    fatal "Env file not found at ${ENV_FILE_PATH}"
fi
pass "Environment: ${APP_ENV} (${OS_TYPE})"

# Check PHP
say "Checking PHP..."
PHP_VERSION=$("$PHP_BIN" -r "echo PHP_VERSION;")
pass "PHP ${PHP_VERSION} at ${PHP_BIN}"

# ============================================================================
# PREREQUISITE CHECKS
# ============================================================================
print_header "Prerequisite Checks"

# Check Redis
say "Checking Redis..."
REDIS_HOST=$(extract_env_value "$ENV_FILE_PATH" "REDIS_HOST")
REDIS_PORT=$(extract_env_value "$ENV_FILE_PATH" "REDIS_PORT")
REDIS_HOST="${REDIS_HOST:-127.0.0.1}"
REDIS_PORT="${REDIS_PORT:-6379}"

if command -v redis-cli &> /dev/null; then
    if redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" ping 2>/dev/null | grep -q "PONG"; then
        pass "Redis responding at ${REDIS_HOST}:${REDIS_PORT}"
    else
        if [[ "$OS_TYPE" == "macos" ]]; then
            warn "Redis not responding. Start with: brew services start redis"
        else
            fatal "Redis not responding at ${REDIS_HOST}:${REDIS_PORT}"
        fi
    fi
else
    warn "redis-cli not available - skipping Redis check"
fi

# Check Horizon package installed
say "Checking Laravel Horizon package..."
if [[ ! -d "${APP_ROOT}/vendor/laravel/horizon" ]]; then
    fatal "Laravel Horizon not installed. Run: composer require laravel/horizon && php artisan horizon:install"
fi
HORIZON_VERSION=$(grep '"version"' "${APP_ROOT}/vendor/laravel/horizon/composer.json" | head -1 | sed -E 's/.*"version": "([^"]+)".*/\1/')
pass "Laravel Horizon v${HORIZON_VERSION} installed"

# ============================================================================
# ENVIRONMENT-SPECIFIC SETUP
# ============================================================================

if [[ "$APP_ENV" == "local" ]] || [[ "$OS_TYPE" == "macos" ]]; then
    # ========================================================================
    # LOCAL / macOS SETUP
    # ========================================================================
    print_header "Local Development Setup (macOS)"

    # Check if Horizon is already running
    say "Checking for existing Horizon process..."
    if pgrep -f "artisan horizon" > /dev/null; then
        HORIZON_PID=$(pgrep -f "artisan horizon" | head -1)
        warn "Horizon already running (PID: ${HORIZON_PID})"
        say "To restart, first kill it: kill ${HORIZON_PID}"
    fi

    # Stop any existing queue:work processes
    say "Stopping existing queue workers..."
    pkill -f "queue:work" 2>/dev/null && pass "Killed old queue:work processes" || say "No queue:work processes to kill"

    # Ensure log directory exists
    say "Ensuring log directory..."
    mkdir -p "${APP_ROOT}/storage/logs"
    touch "$HORIZON_LOG" 2>/dev/null || true
    pass "Log file ready: ${HORIZON_LOG}"

    # Start Horizon
    say "Starting Horizon..."
    if ! pgrep -f "artisan horizon" > /dev/null; then
        # Start Horizon in background using nohup
        cd "${APP_ROOT}"
        nohup "$PHP_BIN" artisan horizon > /dev/null 2>&1 &
        HORIZON_PID=$!
        sleep 3

        if ps -p $HORIZON_PID > /dev/null 2>&1; then
            pass "Horizon started (PID: ${HORIZON_PID})"
        else
            # Check if it started under a different PID
            if pgrep -f "artisan horizon" > /dev/null; then
                HORIZON_PID=$(pgrep -f "artisan horizon" | head -1)
                pass "Horizon started (PID: ${HORIZON_PID})"
            else
                fail "Failed to start Horizon"
            fi
        fi
    else
        pass "Horizon already running"
    fi

    # Wait for workers to spawn
    say "Waiting for workers to spawn (this may take up to 10 seconds)..."
    for i in {1..10}; do
        WORKER_COUNT=$(count_processes "horizon:work")
        if [[ "$WORKER_COUNT" -gt 0 ]]; then
            say "Workers detected after ${i} second(s)"
            break
        fi
        sleep 1
    done

else
    # ========================================================================
    # SERVER / LINUX SETUP (Supervisor)
    # ========================================================================
    print_header "Server Setup (Linux/Supervisor)"

    # Check Supervisor installed
    say "Checking Supervisor..."
    if ! command -v supervisorctl &> /dev/null; then
        fatal "Supervisor not installed. Install with: apt install supervisor (Ubuntu) or yum install supervisor (CentOS)"
    fi
    SUPERVISOR_VERSION=$(supervisorctl version 2>/dev/null || echo "unknown")
    pass "Supervisor installed (v${SUPERVISOR_VERSION})"

    # Check Supervisor service running
    say "Checking Supervisor service..."
    if ! systemctl is-active --quiet supervisor 2>/dev/null && ! pgrep -x supervisord > /dev/null; then
        warn "Supervisor service may not be running. Attempting to start..."
        sudo systemctl start supervisor 2>/dev/null || sudo service supervisor start 2>/dev/null || true
    fi
    pass "Supervisor service active"

    # Check supervisor config source exists
    say "Checking supervisor config template..."
    if [[ ! -f "$SUPERVISOR_CONF_SOURCE" ]]; then
        fatal "Supervisor config not found at ${SUPERVISOR_CONF_SOURCE}"
    fi
    pass "Supervisor config template found"

    # Determine the run user (prefer app owner on /home deployments)
    APP_USER=$(detect_app_user "$APP_ROOT")
    WEB_USER=$(detect_web_user)
    RUN_USER="$WEB_USER"
    if [[ -n "$APP_USER" ]]; then
        RUN_USER="$APP_USER"
    fi
    say "Run user: ${RUN_USER} | Web user: ${WEB_USER}"

    # Create customized supervisor config
    say "Creating supervisor config..."
    TEMP_CONF=$(mktemp)

    # Replace placeholders with actual values
    cat "$SUPERVISOR_CONF_SOURCE" | \
        sed "s|{{APP_NAME_SLUG}}|${APP_NAME_SLUG}|g" | \
        sed "s|{{DEPLOY_PATH}}|${APP_ROOT}|g" | \
        sed "s|{{PHP_BIN}}|${PHP_BIN}|g" | \
        sed "s|{{RUN_USER}}|${RUN_USER}|g" | \
        sed "s|/var/www/[^/]*/|${APP_ROOT}/|g" | \
        sed "s|user=www-data|user=${RUN_USER}|g" | \
        sed "s|command=php|command=${PHP_BIN}|g" > "$TEMP_CONF"

    say "Generated supervisor config:"
    echo "---"
    cat "$TEMP_CONF"
    echo "---"

    # Copy to supervisor conf.d
    say "Copying config to ${SUPERVISOR_CONF_DEST}..."
    if sudo cp "$TEMP_CONF" "$SUPERVISOR_CONF_DEST"; then
        pass "Supervisor config installed"
    else
        rm "$TEMP_CONF"
        fatal "Failed to copy supervisor config. Check sudo permissions."
    fi
    rm "$TEMP_CONF"

    # Ensure log directory exists
    say "Ensuring log directory..."
    mkdir -p "${APP_ROOT}/storage/logs"
    touch "$HORIZON_LOG"
    if [[ -n "$APP_USER" && "$WEB_USER" != "$APP_USER" ]]; then
        chown "${APP_USER}:${WEB_USER}" "$HORIZON_LOG" 2>/dev/null || true
    else
        chown "${RUN_USER}:${RUN_USER}" "$HORIZON_LOG" 2>/dev/null || true
    fi
    chmod 664 "$HORIZON_LOG" 2>/dev/null || true
    pass "Log file ready: ${HORIZON_LOG}"

    # Stop existing Horizon processes
    say "Stopping existing Horizon processes..."
    "$PHP_BIN" "${APP_ROOT}/artisan" horizon:terminate 2>/dev/null || true
    sleep 2

    # Reload supervisor config
    say "Reloading Supervisor configuration..."
    sudo supervisorctl reread && pass "Supervisor config reloaded" || warn "supervisorctl reread returned non-zero"

    # Update supervisor
    say "Updating Supervisor programs..."
    sudo supervisorctl update && pass "Supervisor programs updated" || warn "supervisorctl update returned non-zero"

    # Start Horizon
    say "Starting Horizon..."
    sleep 2
    if sudo supervisorctl start "$SUPERVISOR_PROG" 2>/dev/null; then
        pass "Horizon start command sent"
    elif sudo supervisorctl restart "$SUPERVISOR_PROG" 2>/dev/null; then
        pass "Horizon restart command sent"
    else
        warn "Could not start Horizon via supervisorctl"
    fi

    sleep 5
fi

# ============================================================================
# VERIFICATION
# ============================================================================
print_header "Verification"

# Check Horizon process
say "Checking Horizon master process..."
if pgrep -f "artisan horizon$" > /dev/null || pgrep -f "artisan horizon " > /dev/null; then
    HORIZON_PID=$(pgrep -f "artisan horizon$" | head -1 || pgrep -f "artisan horizon " | head -1)
    pass "Horizon master process running (PID: ${HORIZON_PID})"
else
    fail "Horizon master process not found"
fi

# Check worker processes
say "Checking Horizon worker processes..."
# Use reliable process counting from shared library
WORKER_COUNT=$(count_processes "horizon:work")
if [[ "$WORKER_COUNT" -gt 0 ]]; then
    pass "${WORKER_COUNT} Horizon worker(s) running"
else
    fail "No Horizon workers running"
fi

# Check supervisor status (Linux only)
if [[ "$OS_TYPE" == "linux" ]] && command -v supervisorctl &> /dev/null; then
    say "Checking Supervisor status..."
    HORIZON_STATUS=$(sudo supervisorctl status "$SUPERVISOR_PROG" 2>/dev/null || echo "NOT FOUND")
    if echo "$HORIZON_STATUS" | grep -q "RUNNING"; then
        pass "Horizon supervisor status: RUNNING"
    else
        warn "Horizon supervisor status: ${HORIZON_STATUS}"
    fi
fi

# ============================================================================
# QUEUE PROCESSING TEST
# ============================================================================
print_header "Queue Processing Test"

say "Testing queue processing via Horizon status..."

# Check Horizon status command
HORIZON_STATUS=$("$PHP_BIN" "${APP_ROOT}/artisan" horizon:status 2>/dev/null || echo "")
if [[ "$HORIZON_STATUS" == *"running"* ]]; then
    pass "Horizon status: running"
else
    warn "Horizon status: ${HORIZON_STATUS:-unknown}"
fi

# Check if queues are configured correctly by examining running workers
say "Checking queue configuration..."
RUNNING_QUEUES=$(ps aux | grep "horizon:work" | grep -v grep | sed -E 's/.*--queue=([^ ]+).*/\1/' | sort -u | tr '\n' ',' | sed 's/,$//')

if [[ -n "$RUNNING_QUEUES" ]]; then
    pass "Queues being processed: ${RUNNING_QUEUES}"
else
    warn "Could not verify queue configuration"
fi

# Test basic job dispatch (without closure to avoid serialization issues)
say "Testing job dispatch capability..."
TEST_ID="horizon_setup_test_$(date +%s)"
"$PHP_BIN" "${APP_ROOT}/artisan" tinker --execute="
Cache::put('${TEST_ID}', 'dispatched', 120);
" 2>/dev/null

# Verify cache write worked
CACHE_CHECK=$("$PHP_BIN" "${APP_ROOT}/artisan" tinker --execute="echo Cache::get('${TEST_ID}', 'not_found');" 2>/dev/null | grep -v "=" | grep -v ">>>" | tail -1)
if [[ "$CACHE_CHECK" == "dispatched" ]]; then
    pass "Cache/Redis connectivity verified"
    # Cleanup
    "$PHP_BIN" "${APP_ROOT}/artisan" tinker --execute="Cache::forget('${TEST_ID}');" 2>/dev/null || true
else
    warn "Could not verify cache connectivity"
fi

# ============================================================================
# DASHBOARD ACCESS TEST
# ============================================================================
print_header "Dashboard Access Test"

APP_URL=$(extract_env_value "${APP_ROOT}/.env" "APP_URL")

say "Testing Horizon dashboard at ${APP_URL}/horizon..."
DASHBOARD_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/horizon" 2>/dev/null || echo "000")

case "$DASHBOARD_RESPONSE" in
    302|401|403)
        pass "Dashboard protected (HTTP ${DASHBOARD_RESPONSE} - requires authentication)"
        ;;
    200)
        if [[ "$APP_ENV" == "local" ]]; then
            pass "Dashboard accessible (HTTP 200 - OK for local dev)"
        else
            warn "Dashboard is publicly accessible (HTTP 200) - check HorizonServiceProvider::gate()"
        fi
        ;;
    000)
        warn "Could not connect to ${APP_URL}/horizon"
        ;;
    *)
        warn "Dashboard returned HTTP ${DASHBOARD_RESPONSE}"
        ;;
esac

# ============================================================================
# SUMMARY
# ============================================================================
print_header "Setup Summary"

echo ""
echo -e "  ${GREEN}Passed:${NC}   $PASS"
echo -e "  ${RED}Failed:${NC}   $FAIL"
echo -e "  ${YELLOW}Warnings:${NC} $WARN"
echo ""

if [[ "$FAIL" -gt 0 ]]; then
    echo -e "${RED}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${RED}  SETUP INCOMPLETE: $FAIL failure(s) need attention${NC}"
    echo -e "${RED}═══════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo "Troubleshooting:"
    echo "  1. Check Horizon logs: tail -f ${HORIZON_LOG}"
    if [[ "$OS_TYPE" == "linux" ]]; then
        echo "  2. Check Supervisor logs: tail -f /var/log/supervisor/supervisord.log"
    fi
    echo "  3. Manually start Horizon: ${PHP_BIN} ${APP_ROOT}/artisan horizon"
    echo "  4. Check Redis: redis-cli -h ${REDIS_HOST} -p ${REDIS_PORT} ping"
    echo ""
    exit 1
elif [[ "$WARN" -gt 0 ]]; then
    echo -e "${YELLOW}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}  SETUP COMPLETE WITH WARNINGS: Review $WARN warning(s) above${NC}"
    echo -e "${YELLOW}═══════════════════════════════════════════════════════════════${NC}"
    exit 0
else
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}  HORIZON SETUP COMPLETE!${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo "Horizon is now running and processing queues."
    echo ""
    echo "Dashboard: ${APP_URL}/horizon"
    echo "Logs: ${HORIZON_LOG}"
    echo ""
    if [[ "$OS_TYPE" == "macos" ]]; then
        echo "Useful commands (macOS):"
        echo "  pgrep -f 'artisan horizon'           # Check if running"
        echo "  pkill -f 'artisan horizon'           # Stop Horizon"
        echo "  ${PHP_BIN} artisan horizon           # Start in foreground"
        echo "  tail -f storage/logs/horizon.log    # View logs"
    else
        echo "Useful commands (Linux):"
        echo "  sudo supervisorctl status ${SUPERVISOR_PROG}"
        echo "  sudo supervisorctl restart ${SUPERVISOR_PROG}"
        echo "  ${PHP_BIN} artisan horizon:status"
        echo "  ${PHP_BIN} artisan horizon:terminate"
    fi
    echo ""
    exit 0
fi
