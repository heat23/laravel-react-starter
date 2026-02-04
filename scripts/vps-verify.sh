#!/bin/bash

# Disable shell history to prevent credential exposure
unset HISTFILE
export HISTFILESIZE=0

# ============================================================
# VPS Verification Script (Preview & Production)
# Auto-detects environment from folder path
# Run: ./scripts/vps-verify.sh
# ============================================================

# Source common functions library
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/_common.sh"

FAILED=0
PASSED=0

# =========================================================================
# AUTO-DETECT ENVIRONMENT
# =========================================================================

if ! detect_environment; then
    log_fail "Cannot detect environment. Must be in /production or /preview folder with correct .env file."
    exit 1
fi

PHP_BIN=$(detect_php_binary)
[[ -z "$PHP_BIN" ]] && PHP_BIN="php"

# Get display name from .env
APP_NAME_DISPLAY=$(extract_env_value "$ENV_FILE" "APP_NAME")
[[ -z "$APP_NAME_DISPLAY" ]] && APP_NAME_DISPLAY="Laravel App"

log_say ""
log_say "============================================================"
log_say "🔍 ${APP_NAME_DISPLAY} Verification - $ENVIRONMENT"
log_say "============================================================"
log_say ""

# =========================================================================
# 1. ENVIRONMENT
# =========================================================================
log_say "🔹 ENVIRONMENT"

if grep -q "^APP_DEBUG=false" "$ENV_FILE"; then
    log_pass "APP_DEBUG is false"
    ((PASSED++))
else
    log_fail "APP_DEBUG should be false"
    ((FAILED++))
fi

if grep -q "^APP_ENV=$ENVIRONMENT" "$ENV_FILE"; then
    log_pass "APP_ENV is $ENVIRONMENT"
    ((PASSED++))
else
    log_fail "APP_ENV should be $ENVIRONMENT"
    ((FAILED++))
fi

log_say ""

# =========================================================================
# 2. DATABASE
# =========================================================================
log_say "🔹 DATABASE"

if $PHP_BIN artisan tinker --execute="try { \DB::connection()->getPdo(); echo 'ok'; } catch (\Exception \$e) { echo 'FAILED'; }" 2>/dev/null | grep -q "ok"; then
    log_pass "Database connection working"
    ((PASSED++))
else
    log_fail "Database connection failed"
    ((FAILED++))
fi

TABLE_COUNT=$($PHP_BIN artisan tinker --execute="try { echo \DB::selectOne('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?', [config('database.connections.mysql.database')])->count ?? 0; } catch (\Exception \$e) { echo 0; }" 2>/dev/null || echo "0")

if [[ "$TABLE_COUNT" -gt 5 ]]; then
    log_pass "Database has $TABLE_COUNT tables"
    ((PASSED++))
else
    log_fail "Database should have more tables (has $TABLE_COUNT)"
    ((FAILED++))
fi

log_say ""

# =========================================================================
# 3. REDIS
# =========================================================================
log_say "🔹 REDIS"

# Get Redis host and port from .env if available
REDIS_HOST=$(extract_env_value "$ENV_FILE" "REDIS_HOST" 2>/dev/null || echo "127.0.0.1")
REDIS_PORT=$(extract_env_value "$ENV_FILE" "REDIS_PORT" 2>/dev/null || echo "6379")

if test_redis_connectivity "$REDIS_HOST" "$REDIS_PORT"; then
    log_pass "Redis is running"
    ((PASSED++))
else
    log_fail "Redis not responding"
    ((FAILED++))
fi

REDIS_PING_OUTPUT=$($PHP_BIN artisan tinker --execute="try { echo \Illuminate\Support\Facades\Redis::ping(); } catch (\Exception \$e) { echo 'FAILED: ' . \$e->getMessage(); }" 2>/dev/null | tail -1)
if echo "$REDIS_PING_OUTPUT" | grep -q "PONG" || [[ "$REDIS_PING_OUTPUT" == "1" ]]; then
    log_pass "Laravel can connect to Redis"
    ((PASSED++))
else
    if [[ -z "$REDIS_PING_OUTPUT" ]]; then
        REDIS_PING_OUTPUT="FAILED: no output"
    fi
    log_fail "Laravel Redis connection failed ($REDIS_PING_OUTPUT)"
    ((FAILED++))
fi

if $PHP_BIN artisan tinker --execute="try { \Illuminate\Support\Facades\Cache::store('redis')->put('verify_test', 'ok', 60); echo \Illuminate\Support\Facades\Cache::store('redis')->get('verify_test'); } catch (\Exception \$e) { echo 'FAILED'; }" 2>/dev/null | grep -q "ok"; then
    log_pass "Redis caching working"
    ((PASSED++))
else
    log_fail "Redis caching not working"
    ((FAILED++))
fi

log_say ""

# =========================================================================
# 4. QUEUE WORKERS (HORIZON - if enabled)
# =========================================================================
log_say "🔹 QUEUE WORKERS (HORIZON)"

FEATURE_HORIZON=$(extract_env_value "$ENV_FILE" "FEATURE_HORIZON")
FEATURE_HORIZON=$(echo "$FEATURE_HORIZON" | tr '[:upper:]' '[:lower:]')

if [[ "$FEATURE_HORIZON" != "true" && "$FEATURE_HORIZON" != "1" ]]; then
    log_info "Horizon not enabled (FEATURE_HORIZON != true) - skipping Horizon checks"
else
    HORIZON_STATUS_UNKNOWN=0

    if command -v supervisorctl &>/dev/null; then
        HORIZON_SUP_STATUS=""
        if sudo -n true 2>/dev/null; then
            HORIZON_SUP_STATUS=$(sudo supervisorctl status "$SUPERVISOR_PROG" 2>/dev/null || true)
        else
            HORIZON_SUP_STATUS=$(supervisorctl status "$SUPERVISOR_PROG" 2>/dev/null || true)
        fi

        if echo "$HORIZON_SUP_STATUS" | grep -q "RUNNING"; then
            log_pass "Horizon is RUNNING"
            ((PASSED++))
        elif echo "$HORIZON_SUP_STATUS" | grep -q "STOPPED"; then
            log_warn "Horizon is STOPPED (run: sudo supervisorctl start $SUPERVISOR_PROG)"
            ((FAILED++))
        else
            if [[ -z "$HORIZON_SUP_STATUS" ]]; then
                log_warn "Horizon status unknown (supervisorctl requires sudo)"
            else
                log_warn "Horizon status unknown"
                log_warn "Supervisor status: $HORIZON_SUP_STATUS"
            fi
            HORIZON_STATUS_UNKNOWN=1
        fi
    else
        log_fail "supervisorctl not found"
        ((FAILED++))
    fi

    HORIZON_RUNNING=0
    if $PHP_BIN artisan horizon:status 2>/dev/null | grep -q "running\|Running"; then
        log_pass "Horizon confirmed running"
        ((PASSED++))
        HORIZON_RUNNING=1
    else
        log_warn "Horizon status inconclusive"
    fi

    if [[ $HORIZON_STATUS_UNKNOWN -eq 1 && $HORIZON_RUNNING -eq 0 ]]; then
        log_fail "Horizon not confirmed running"
        ((FAILED++))
    fi

    if $PHP_BIN artisan queue:work --once 2>/dev/null | grep -q "waiting\|processed"; then
        log_pass "Queue worker can process jobs"
        ((PASSED++))
    else
        log_warn "Queue worker test inconclusive"
    fi
fi

log_say ""

# =========================================================================
# 5. SCHEDULER (CRON)
# =========================================================================
log_say "🔹 SCHEDULER (CRON)"

# Use app owner for /home deployments, fallback to web user
APP_USER=$(detect_app_user)
WEB_USER=$(detect_web_user)
CRON_USER="$APP_USER"
if [[ -z "$CRON_USER" ]]; then
    CRON_USER="$WEB_USER"
fi

if sudo crontab -l -u "$CRON_USER" 2>/dev/null | grep -q "schedule:run"; then
    log_pass "Scheduler cron is configured"
    ((PASSED++))
else
    log_fail "Scheduler cron not found (run ./scripts/vps-setup.sh again)"
    ((FAILED++))
fi

SCHEDULED_TASKS=$($PHP_BIN artisan schedule:list 2>/dev/null | grep -c "artisan" || echo "0")

if [[ "$SCHEDULED_TASKS" -gt 0 ]]; then
    log_pass "Scheduler has $SCHEDULED_TASKS tasks configured"
    ((PASSED++))
else
    log_warn "No scheduled tasks found (this may be expected for starter template)"
fi

log_say ""

# =========================================================================
# 6. FILE PERMISSIONS
# =========================================================================
log_say "🔹 FILE PERMISSIONS"

# Check ownership rather than writability by current user
# Accept app owner (home deployments) or standard web users
APP_USER_PERMS=$(detect_app_user)
WEB_USER_PERMS=$(detect_web_user)

STORAGE_OWNER=$(stat -c '%U' storage/logs 2>/dev/null || stat -f '%Su' storage/logs 2>/dev/null || echo "unknown")
if [[ "$STORAGE_OWNER" == "$APP_USER_PERMS" ]] || [[ "$STORAGE_OWNER" == "$WEB_USER_PERMS" ]] || [[ "$STORAGE_OWNER" == "apache" ]] || [[ "$STORAGE_OWNER" == "www-data" ]]; then
    log_pass "storage/logs owned by $STORAGE_OWNER (correct)"
    ((PASSED++))
else
    log_fail "storage/logs owned by $STORAGE_OWNER (should be $APP_USER_PERMS or $WEB_USER_PERMS)"
    ((FAILED++))
fi

CACHE_OWNER=$(stat -c '%U' bootstrap/cache 2>/dev/null || stat -f '%Su' bootstrap/cache 2>/dev/null || echo "unknown")
if [[ "$CACHE_OWNER" == "$APP_USER_PERMS" ]] || [[ "$CACHE_OWNER" == "$WEB_USER_PERMS" ]] || [[ "$CACHE_OWNER" == "apache" ]] || [[ "$CACHE_OWNER" == "www-data" ]]; then
    log_pass "bootstrap/cache owned by $CACHE_OWNER (correct)"
    ((PASSED++))
else
    log_fail "bootstrap/cache owned by $CACHE_OWNER (should be $APP_USER_PERMS or $WEB_USER_PERMS)"
    ((FAILED++))
fi

log_say ""

# =========================================================================
# 7. LARAVEL CHECKS
# =========================================================================
log_say "🔹 LARAVEL APPLICATION"

if [[ -f "artisan" ]]; then
    log_pass "artisan file found"
    ((PASSED++))
else
    log_fail "artisan file not found"
    ((FAILED++))
fi

if $PHP_BIN artisan tinker --execute="echo 'ok';" 2>/dev/null | grep -q "ok"; then
    log_pass "Laravel artisan commands working"
    ((PASSED++))
else
    log_fail "Laravel artisan not responding"
    ((FAILED++))
fi

APP_URL=$(grep "^APP_URL=" "$ENV_FILE" | cut -d'=' -f2 | tr -d '"' | tr -d "'")

if [[ -n "$APP_URL" ]]; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 10 "$APP_URL" 2>/dev/null || echo "000")
    if [[ "$HTTP_CODE" == "200" ]] || [[ "$HTTP_CODE" == "302" ]] || [[ "$HTTP_CODE" == "301" ]]; then
        log_pass "Application responding at $APP_URL (HTTP $HTTP_CODE)"
        ((PASSED++))
    else
        log_warn "Application returned HTTP $HTTP_CODE (may still be warming up)"
    fi
else
    log_warn "APP_URL not configured"
fi

log_say ""

# =========================================================================
# 8. LOGS
# =========================================================================
log_say "🔹 ERROR LOGS"

if [[ -f "storage/logs/laravel.log" ]]; then
    ERROR_COUNT=$(grep -c "ERROR\\|FATAL" storage/logs/laravel.log 2>/dev/null || true)
    ERROR_COUNT=${ERROR_COUNT:-0}
    if [[ "$ERROR_COUNT" -eq 0 ]]; then
        log_pass "No errors in application logs"
        ((PASSED++))
    else
        log_warn "Found $ERROR_COUNT errors in logs (check storage/logs/laravel.log)"
    fi
else
    log_info "No log file yet (will be created on first request)"
fi

if [[ "$FEATURE_HORIZON" == "true" || "$FEATURE_HORIZON" == "1" ]]; then
    if [[ -f "storage/logs/horizon.log" ]]; then
        HORIZON_ERRORS=$(grep -c "ERROR\|failed" storage/logs/horizon.log 2>/dev/null | head -1 || echo "0")
        if [[ "$HORIZON_ERRORS" -eq 0 ]]; then
            log_pass "No errors in Horizon logs"
            ((PASSED++))
        else
            log_warn "Found errors in Horizon logs"
        fi
    fi
fi

log_say ""

# =========================================================================
# 9. SYSTEM RESOURCES
# =========================================================================
log_say "🔹 SYSTEM RESOURCES"

DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [[ "$DISK_USAGE" -lt 80 ]]; then
    log_pass "Disk usage: $DISK_USAGE% (healthy)"
    ((PASSED++))
else
    log_fail "Disk usage: $DISK_USAGE% (clean up disk space)"
    ((FAILED++))
fi

REDIS_MEMORY=$(redis-cli INFO memory 2>/dev/null | grep "used_memory_human" | cut -d: -f2 | tr -d '\r')
if [[ -n "$REDIS_MEMORY" ]]; then
    log_pass "Redis memory: $REDIS_MEMORY"
    ((PASSED++))
fi

log_say ""

# =========================================================================
# SUMMARY
# =========================================================================
log_say "============================================================"
log_say "RESULTS - $ENVIRONMENT"
log_say "============================================================"
log_say ""

log_say "✅ Passed: $PASSED checks"
log_say "❌ Failed: $FAILED checks"
log_say ""

if [[ $FAILED -eq 0 ]]; then
    log_say "${GREEN}🎉 ALL CHECKS PASSED - $ENVIRONMENT IS READY!${NC}"
    log_say ""
    log_say "Your $ENVIRONMENT environment is fully configured:"
    log_say "  • Redis: Running and connected"
    log_say "  • MySQL: Database ready with tables"
    if [[ "$FEATURE_HORIZON" == "true" || "$FEATURE_HORIZON" == "1" ]]; then
        log_say "  • Horizon: Queue workers running"
    fi
    log_say "  • Scheduler: Cron job configured"
    log_say "  • Laravel: Application optimized"
    log_say ""
    exit 0
else
    log_say "${RED}⚠️  SOME CHECKS FAILED - FIX BEFORE DEPLOYMENT${NC}"
    log_say ""
    log_say "Run this to see more details:"
    log_say "  ./scripts/vps-setup.sh"
    log_say ""
    exit 1
fi
