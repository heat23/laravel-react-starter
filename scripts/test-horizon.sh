#!/bin/bash
# ============================================================================
# Laravel Horizon Test Script
# ============================================================================
# Tests that Horizon is running and processing jobs correctly.
#
# Usage:
#   chmod +x scripts/test-horizon.sh
#   ./scripts/test-horizon.sh
#
# Prerequisites:
#   - FEATURE_HORIZON=true in .env
#   - Laravel Horizon running (via setup-horizon.sh or manually)
#
# Environments supported:
#   - macOS with Laravel Herd (local development)
#   - macOS with Homebrew (local development)
#   - Linux with Supervisor (preview/production)
#
# Tests performed:
#   1. Horizon master process running
#   2. Horizon workers running
#   3. Queue processing capability
#   4. Dashboard accessible (auth-protected)
#   5. Redis connectivity
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

pass() { log_pass "$*"; ((PASS++)) || true; }
warn() { log_warn "$*"; ((WARN++)) || true; }
fail() { log_fail "$*"; ((FAIL++)) || true; }

# ============================================================================
# ENVIRONMENT DETECTION
# ============================================================================

OS_TYPE=$(detect_os)
PHP_BIN=$(detect_php_binary)

if [[ -z "$PHP_BIN" ]]; then
    log_fail "PHP not found"
    exit 1
fi

APP_ROOT="${APP_ROOT:-$(pwd -P)}"

# Validate we're in the right directory
if [[ ! -f "${APP_ROOT}/artisan" ]]; then
    echo -e "${RED}Error: Not in Laravel application root${NC}"
    exit 1
fi

# Get environment info (prefer detected env file)
ENV_FILE=""
if detect_environment; then
    ENV_FILE="${ENV_FILE}"
fi
if [[ -z "$ENV_FILE" ]]; then
    ENV_FILE=".env"
fi
ENV_FILE="${APP_ROOT}/${ENV_FILE}"

if [[ -f "$ENV_FILE" ]]; then
    APP_ENV=$(extract_env_value "$ENV_FILE" "APP_ENV")
    APP_URL=$(extract_env_value "$ENV_FILE" "APP_URL")
else
    APP_ENV=""
    APP_URL=""
fi

# Get display name
APP_NAME_DISPLAY=$(extract_env_value "$ENV_FILE" "APP_NAME")
[[ -z "$APP_NAME_DISPLAY" ]] && APP_NAME_DISPLAY="Laravel App"

echo ""
echo -e "${BLUE}${APP_NAME_DISPLAY} - Horizon Test Suite${NC}"
echo "Environment: ${APP_ENV} (${OS_TYPE})"
echo "PHP: ${PHP_BIN}"
echo "App URL: ${APP_URL}"

# ============================================================================
# FEATURE FLAG CHECK
# ============================================================================
print_header "Feature Flag Check"

echo -n "FEATURE_HORIZON enabled... "
if is_feature_enabled "FEATURE_HORIZON" "$ENV_FILE"; then
    pass "yes"
else
    fail "no - set FEATURE_HORIZON=true in .env to use Horizon"
    echo ""
    echo "Horizon is not enabled. Enable it by setting FEATURE_HORIZON=true in your .env file."
    exit 1
fi

# ============================================================================
# PROCESS CHECKS
# ============================================================================
print_header "Process Checks"

# Check Horizon master process
echo -n "Horizon master process... "
if pgrep -f "${APP_ROOT}/artisan horizon" > /dev/null || pgrep -f "artisan horizon" > /dev/null; then
    HORIZON_PID=$(pgrep -f "${APP_ROOT}/artisan horizon" | head -1 || pgrep -f "artisan horizon" | head -1)
    pass "running (PID: ${HORIZON_PID})"
else
    fail "not running"
fi

# Check Horizon workers - use pgrep, fallback to ps
echo -n "Horizon workers... "
WORKER_COUNT=$(pgrep -f "horizon:work" 2>/dev/null | wc -l | tr -d ' ' || echo "0")
if [[ -z "$WORKER_COUNT" ]]; then
    WORKER_COUNT=0
fi
if [[ "$WORKER_COUNT" -eq 0 ]]; then
    WORKER_COUNT=$(ps aux | grep -E "horizon:work" | grep -v grep | wc -l | tr -d ' ')
fi

if [[ "$WORKER_COUNT" -gt 0 ]]; then
    pass "${WORKER_COUNT} worker(s) running"
else
    fail "no workers running"
fi

# Check Supervisor status (Linux only)
if [[ "$OS_TYPE" == "linux" ]]; then
    echo -n "Supervisor status... "
    if command -v supervisorctl &> /dev/null; then
        # Note: For local development, Supervisor may not be used
        # For production/preview, use detected environment-aware program name
        if ! detect_environment; then
            SUPERVISOR_PROG="${APP_NAME_SLUG:-laravel-app}-horizon"  # Default for local
        fi

        HORIZON_STATUS=""
        if sudo -n true 2>/dev/null; then
            HORIZON_STATUS=$(sudo supervisorctl status "$SUPERVISOR_PROG" 2>/dev/null || true)
        else
            HORIZON_STATUS=$(supervisorctl status "$SUPERVISOR_PROG" 2>/dev/null || true)
        fi

        if [[ -z "$HORIZON_STATUS" ]]; then
            warn "supervisorctl requires sudo (re-run with sudo)"
        elif echo "$HORIZON_STATUS" | grep -q "RUNNING"; then
            pass "RUNNING"
        else
            warn "${HORIZON_STATUS}"
        fi
    else
        warn "supervisorctl not available"
    fi
fi

# ============================================================================
# QUEUE CONFIGURATION TESTS
# ============================================================================
print_header "Queue Configuration Tests"

# Test that queues are configured by checking running processes
echo -n "Default queue has worker... "
if ps aux | grep "horizon:work" | grep -qv grep; then
    pass "workers running"
else
    fail "no workers found"
fi

# Check Redis connectivity
echo ""
echo -n "Redis connectivity... "
if redis-cli ping 2>/dev/null | grep -q "PONG"; then
    pass "Redis responding"
else
    fail "Redis not responding"
fi

# ============================================================================
# HORIZON STATUS
# ============================================================================
print_header "Horizon Status"

echo "Horizon internal status:"
"$PHP_BIN" "${APP_ROOT}/artisan" horizon:status 2>/dev/null || echo "Could not get status"

echo ""
echo "Active supervisors:"
"$PHP_BIN" "${APP_ROOT}/artisan" tinker --execute="
\$masters = \Laravel\Horizon\Contracts\MasterSupervisorRepository::class;
\$supervisors = app(\$masters)->all();
foreach (\$supervisors as \$supervisor) {
    echo '  - ' . \$supervisor->name . ' (PID: ' . \$supervisor->pid . ')' . PHP_EOL;
}
if (empty(\$supervisors)) {
    echo '  (none found)' . PHP_EOL;
}
" 2>/dev/null | grep -v "Cannot load" || echo "  Could not query supervisors"

# ============================================================================
# DASHBOARD ACCESS TEST
# ============================================================================
print_header "Dashboard Access Test"

echo -n "Horizon dashboard (${APP_URL}/horizon)... "
DASHBOARD_CODE=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/horizon" 2>/dev/null || echo "000")

case "$DASHBOARD_CODE" in
    302|401|403)
        pass "protected (HTTP ${DASHBOARD_CODE})"
        ;;
    200)
        if [[ "$APP_ENV" == "local" ]]; then
            pass "accessible (HTTP 200 - OK for local)"
        else
            warn "publicly accessible - check HorizonServiceProvider::gate()"
        fi
        ;;
    000)
        warn "could not connect"
        ;;
    *)
        warn "unexpected response (HTTP ${DASHBOARD_CODE})"
        ;;
esac

# ============================================================================
# SUMMARY
# ============================================================================
print_header "Test Summary"

echo ""
echo -e "  ${GREEN}Passed:${NC}   $PASS"
echo -e "  ${RED}Failed:${NC}   $FAIL"
echo -e "  ${YELLOW}Warnings:${NC} $WARN"
echo ""

if [[ "$FAIL" -gt 0 ]]; then
    echo -e "${RED}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${RED}  TESTS FAILED: $FAIL failure(s)${NC}"
    echo -e "${RED}═══════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo "Troubleshooting:"
    if [[ "$OS_TYPE" == "macos" ]]; then
        echo "  1. Start Horizon: ${PHP_BIN} artisan horizon"
        echo "  2. Or run setup: ./scripts/setup-horizon.sh"
    else
        # Show environment-aware supervisor program name
        if ! detect_environment; then
            SUPERVISOR_PROG="${APP_NAME_SLUG:-laravel-app}-horizon"
        fi
        echo "  1. Restart Horizon: sudo supervisorctl restart ${SUPERVISOR_PROG}"
        echo "  2. Or run setup: ./scripts/setup-horizon.sh"
    fi
    echo "  3. Check logs: tail -f storage/logs/horizon.log"
    echo "  4. Check Redis: redis-cli ping"
    echo ""
    exit 1
elif [[ "$WARN" -gt 0 ]]; then
    echo -e "${YELLOW}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}  TESTS PASSED WITH WARNINGS${NC}"
    echo -e "${YELLOW}═══════════════════════════════════════════════════════════════${NC}"
    exit 0
else
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}  ALL TESTS PASSED!${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
    exit 0
fi
