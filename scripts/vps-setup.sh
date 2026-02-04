#!/bin/bash
set -e

# Disable shell history to prevent credential exposure
unset HISTFILE
export HISTFILESIZE=0

# ============================================================
# VPS Automatic Setup Script (Preview & Production)
# Auto-detects environment from folder path
# Run: ./scripts/vps-setup.sh
# ============================================================

# Source common functions library
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/_common.sh"

# =========================================================================
# PHP BINARY DETECTION (with version verification)
# =========================================================================

PHP_BIN=$(detect_php_binary)
if [[ -z "$PHP_BIN" ]]; then
    log_fail "PHP 8.0+ not found"
    exit 1
fi

PHP_VERSION=$("$PHP_BIN" --version | head -1)
if [[ ! "$PHP_VERSION" =~ "8." ]]; then
    log_fail "PHP 8.0+ required, found: $PHP_VERSION"
    exit 1
fi

# =========================================================================
# AUTO-DETECT ENVIRONMENT
# =========================================================================

if ! detect_environment; then
    log_fail "Cannot detect environment. Must be in /production or /preview folder with correct .env file."
    exit 1
fi

# Supervisor config directory (detect from supervisord.conf if possible)
SUPERVISOR_DIR="$(detect_supervisor_dir)"
SUPERVISOR_EXT="$(detect_supervisor_ext)"
HORIZON_CONFIG="${SUPERVISOR_DIR}/${SUPERVISOR_PROG}.${SUPERVISOR_EXT}"

# Get display name from .env
APP_NAME_DISPLAY=$(extract_env_value "$ENV_FILE" "APP_NAME")
[[ -z "$APP_NAME_DISPLAY" ]] && APP_NAME_DISPLAY="Laravel App"

log_say ""
log_say "============================================================"
log_say "🚀 ${APP_NAME_DISPLAY} VPS Setup ($ENVIRONMENT)"
log_say "============================================================"
log_say "Environment: $ENVIRONMENT"
log_say "Path: $(pwd)"
log_say "Config: $ENV_FILE"
log_say "PHP: $($PHP_BIN --version | head -1)"
log_say "App slug: $APP_NAME_SLUG"
log_say "Supervisor dir: $SUPERVISOR_DIR"
log_say "Supervisor ext: .$SUPERVISOR_EXT"
log_say "Horizon config: $HORIZON_CONFIG"
log_say ""

# =========================================================================
# 1. ENVIRONMENT VALIDATION
# =========================================================================
log_say "Step 1: Validating environment..."

if [[ ! -f "$ENV_FILE" ]]; then
    log_fail "$ENV_FILE not found. Copy from .env.example first."
    exit 1
fi

# Extract critical values (safely, without exposing to history)
DB_HOST=$(extract_env_value "$ENV_FILE" "DB_HOST")
DB_DATABASE=$(extract_env_value "$ENV_FILE" "DB_DATABASE")
DB_USERNAME=$(extract_env_value "$ENV_FILE" "DB_USERNAME")
DB_PASSWORD=$(extract_env_value "$ENV_FILE" "DB_PASSWORD")
REDIS_HOST=$(extract_env_value "$ENV_FILE" "REDIS_HOST")
REDIS_PORT=$(extract_env_value "$ENV_FILE" "REDIS_PORT")
APP_URL=$(extract_env_value "$ENV_FILE" "APP_URL")

# Helper to update .env values safely (cross-platform sed)
update_env_value() {
    local key="$1"
    local value="$2"
    if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i.bak "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
        rm -f "${ENV_FILE}.bak"
        return 0
    fi
    return 1
}

# Validate critical values are set
[[ -z "$DB_HOST" ]] && { log_fail "DB_HOST not configured in $ENV_FILE"; exit 1; }
[[ -z "$DB_DATABASE" ]] && { log_fail "DB_DATABASE not configured in $ENV_FILE"; exit 1; }
[[ -z "$DB_USERNAME" ]] && { log_fail "DB_USERNAME not configured in $ENV_FILE"; exit 1; }
[[ -z "$DB_PASSWORD" ]] && { log_fail "DB_PASSWORD not configured in $ENV_FILE"; exit 1; }
[[ -z "$REDIS_HOST" ]] && { log_fail "REDIS_HOST not configured in $ENV_FILE"; exit 1; }
[[ -z "$REDIS_PORT" ]] && { log_fail "REDIS_PORT not configured in $ENV_FILE"; exit 1; }

# Check APP_DEBUG and APP_ENV
APP_DEBUG=$(extract_env_value "$ENV_FILE" "APP_DEBUG")
APP_ENV=$(extract_env_value "$ENV_FILE" "APP_ENV")

if [[ "$APP_DEBUG" != "false" ]]; then
    log_warn "APP_DEBUG should be 'false' in production/preview (currently: $APP_DEBUG)"
    if update_env_value "APP_DEBUG" "false"; then
        APP_DEBUG="false"
        log_pass "APP_DEBUG set to false in $ENV_FILE"
    fi
fi

if [[ "$APP_ENV" != "$ENVIRONMENT" ]]; then
    log_warn "APP_ENV should be '$ENVIRONMENT' (currently: $APP_ENV)"
    if update_env_value "APP_ENV" "$ENVIRONMENT"; then
        APP_ENV="$ENVIRONMENT"
        log_pass "APP_ENV set to $ENVIRONMENT in $ENV_FILE"
    fi
fi

log_pass "Environment file validated"
log_say ""

# =========================================================================
# 2. INSTALL REDIS
# =========================================================================
log_say "Step 2: Setting up Redis..."

# Check Redis requirements
log_info "Checking Redis prerequisites..."
if ! command -v redis-cli &>/dev/null; then
    log_info "Installing Redis..."
    if [[ -f /etc/debian_version ]]; then
        sudo apt-get update >/dev/null 2>&1
        sudo apt-get install -y redis-server >/dev/null 2>&1
    elif [[ -f /etc/redhat-release ]]; then
        sudo yum install -y redis >/dev/null 2>&1
    fi
else
    log_info "redis-cli found at $(command -v redis-cli)"
fi

# Test if Redis is already accessible
log_info "Testing Redis connection to $REDIS_HOST:$REDIS_PORT..."
if test_redis_connectivity "$REDIS_HOST" "$REDIS_PORT"; then
    log_pass "Redis is already running"
else
    # Try to start Redis
    log_info "Starting Redis..."
    if sudo systemctl start redis 2>/dev/null; then
        log_info "Redis start command sent"
    else
        log_warn "systemctl start redis may have failed"
    fi

    # Wait for Redis to start
    sleep 2

    # Test again
    log_info "Re-testing Redis connection..."
    if test_redis_connectivity "$REDIS_HOST" "$REDIS_PORT"; then
        log_pass "Redis started successfully"
        # Enable auto-start
        sudo systemctl enable redis >/dev/null 2>&1 || true
    else
        # Check if redis-cli command exists
        if ! command -v redis-cli &>/dev/null; then
            log_fail "redis-cli command not found. Redis may not be installed."
            exit 1
        fi

        # Check if redis-server is running
        if ! sudo systemctl is-active redis >/dev/null 2>&1; then
            log_fail "Redis service is not running. Start it manually: sudo systemctl start redis"
            exit 1
        fi

        # If we get here, Redis is running but not responding
        log_fail "Redis not accessible at $REDIS_HOST:$REDIS_PORT. Check REDIS_HOST and REDIS_PORT in $ENV_FILE"
        exit 1
    fi
fi

log_say ""

# =========================================================================
# 3. MYSQL DATABASE
# =========================================================================
log_say "Step 3: Setting up MySQL database..."

# Check if database exists (test connection using MYSQL_PWD to avoid password in process list)
DB_TEST=$(MYSQL_PWD="$DB_PASSWORD" mysql -h "$DB_HOST" -u "$DB_USERNAME" "$DB_DATABASE" -e "SELECT 1;" 2>/dev/null || echo "FAILED")

if [[ "$DB_TEST" == "FAILED" ]]; then
    # Try to create the database (requires root/admin access)
    log_info "Database doesn't exist or connection failed. Attempting to create..."
    log_info "Note: This requires MySQL root access. If it fails, create the database manually."
    CREATE_RESULT=$(MYSQL_PWD="$DB_PASSWORD" mysql -h "$DB_HOST" -u "$DB_USERNAME" -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1)

    if [[ $? -ne 0 ]]; then
        log_fail "Failed to create database. Create it manually:"
        log_info "  mysql -u root -p -e \"CREATE DATABASE $DB_DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
        log_info "  mysql -u root -p -e \"GRANT ALL ON $DB_DATABASE.* TO '$DB_USERNAME'@'localhost';\""
        exit 1
    fi
    log_pass "Database created"
else
    log_pass "Database connection verified"
fi

# Run migrations with proper error handling
log_info "Running migrations..."
MIGRATION_OUTPUT=$("$PHP_BIN" artisan migrate --force 2>&1)
MIGRATION_STATUS=$?

if [[ $MIGRATION_STATUS -ne 0 ]]; then
    log_fail "Migration failed with status $MIGRATION_STATUS"
    echo "$MIGRATION_OUTPUT"
    exit 1
fi

# Verify migrations completed without errors (check exit code is sufficient)
if echo "$MIGRATION_OUTPUT" | grep -qi "FAILED\|Error\|exception"; then
    log_fail "Migration encountered errors:"
    echo "$MIGRATION_OUTPUT"
    exit 1
fi

# Count tables - should have tables after migrations
TABLE_COUNT=$(MYSQL_PWD="$DB_PASSWORD" mysql -h "$DB_HOST" -u "$DB_USERNAME" "$DB_DATABASE" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_DATABASE';" 2>/dev/null | tail -1)

if [[ -z "$TABLE_COUNT" ]] || [[ "$TABLE_COUNT" -lt 5 ]]; then
    log_warn "Database has fewer than expected tables ($TABLE_COUNT). Migrations may be incomplete."
else
    log_pass "Database ready with $TABLE_COUNT tables"
fi

log_say ""

# =========================================================================
# 4. HORIZON QUEUE WORKERS (if enabled)
# =========================================================================
log_say "Step 4: Setting up Horizon queue workers..."

# Check if Horizon feature is enabled
FEATURE_HORIZON=$(extract_env_value "$ENV_FILE" "FEATURE_HORIZON")
FEATURE_HORIZON=$(echo "$FEATURE_HORIZON" | tr '[:upper:]' '[:lower:]')

if [[ "$FEATURE_HORIZON" != "true" && "$FEATURE_HORIZON" != "1" ]]; then
    log_info "Horizon not enabled (FEATURE_HORIZON != true) - skipping Horizon setup"
    log_info "To enable Horizon, set FEATURE_HORIZON=true in $ENV_FILE"
else
    # Check if supervisor is installed
    if ! command -v supervisorctl &>/dev/null; then
        log_info "Installing Supervisor..."
        if [[ -f /etc/debian_version ]]; then
            sudo apt-get install -y supervisor >/dev/null 2>&1
        elif [[ -f /etc/redhat-release ]]; then
            sudo yum install -y supervisor >/dev/null 2>&1
        fi
    fi

    # Get app directory (resolve symlinks)
    APP_DIR="$(pwd -P)"

    # Determine app user and web user
    APP_USER=$(detect_app_user "$APP_DIR")
    if [[ -z "$APP_USER" ]]; then
        APP_USER=$(detect_web_user)
    fi
    WEB_USER=$(detect_web_user)
    log_info "App user: ${APP_USER:-unknown} | Web user: ${WEB_USER:-unknown}"

    # Create/Update supervisor config
    NEED_CONFIG=0
    CONFIG_ACTION="Creating"
    if [[ -f "$HORIZON_CONFIG" ]]; then
        # Update if command path, directory, or user do not match expectations
        if ! grep -Fq "$APP_DIR/artisan" "$HORIZON_CONFIG" \
            || ! grep -Fq "user=$APP_USER" "$HORIZON_CONFIG" \
            || ! grep -Fq "directory=$APP_DIR" "$HORIZON_CONFIG"; then
            NEED_CONFIG=1
            CONFIG_ACTION="Updating"
        else
            NEED_CONFIG=0
        fi
    else
        NEED_CONFIG=1
    fi

    if [[ $NEED_CONFIG -eq 1 ]]; then
        log_info "$CONFIG_ACTION Supervisor config for $ENVIRONMENT..."

        # Ensure supervisor config directory exists (Red Hat/CentOS uses /etc/supervisord.d/)
        if [[ ! -d "$SUPERVISOR_DIR" ]]; then
            log_info "Creating supervisor config directory: $SUPERVISOR_DIR"
            sudo mkdir -p "$SUPERVISOR_DIR" || { log_fail "Failed to create $SUPERVISOR_DIR"; exit 1; }
        fi

        # Write to temp file first (handles paths with spaces safely)
        TEMP_CONFIG="/tmp/horizon-$$.conf"

        cat > "$TEMP_CONFIG" <<'EOF'
[program:SUPERVISOR_PROG_PLACEHOLDER]
process_name=%(program_name)s
command=PHP_BIN_PLACEHOLDER "APPDIR_PLACEHOLDER/artisan" horizon
directory=APPDIR_PLACEHOLDER
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=WEB_USER_PLACEHOLDER
numprocs=1
redirect_stderr=true
stdout_logfile=APPDIR_PLACEHOLDER/storage/logs/horizon.log
stderr_logfile=APPDIR_PLACEHOLDER/storage/logs/horizon.error.log
stopwaitsecs=3600
environment=PATH="/opt/cpanel/ea-php84/root/usr/bin:/usr/local/bin:/usr/bin:/bin"
EOF

        # Replace placeholders
        sed -i "s|SUPERVISOR_PROG_PLACEHOLDER|$SUPERVISOR_PROG|g" "$TEMP_CONFIG"
        sed -i "s|PHP_BIN_PLACEHOLDER|$PHP_BIN|g" "$TEMP_CONFIG"
        sed -i "s|APPDIR_PLACEHOLDER|$APP_DIR|g" "$TEMP_CONFIG"
        sed -i "s|WEB_USER_PLACEHOLDER|$APP_USER|g" "$TEMP_CONFIG"

        # Move into place atomically
        sudo mv "$TEMP_CONFIG" "$HORIZON_CONFIG"
        sudo chmod 644 "$HORIZON_CONFIG"
        if [[ "$CONFIG_ACTION" == "Updating" ]]; then
            log_pass "Supervisor config updated"
        else
            log_pass "Supervisor config created"
        fi
    else
        log_pass "Supervisor config already exists"
    fi

    # Ensure Horizon log file exists and is writable by app user
    HORIZON_LOG="${APP_DIR}/storage/logs/horizon.log"
    HORIZON_ERR_LOG="${APP_DIR}/storage/logs/horizon.error.log"
    if [[ -n "$APP_USER" ]]; then
        mkdir -p "${APP_DIR}/storage/logs" 2>/dev/null || true
        touch "$HORIZON_LOG" 2>/dev/null || true
        touch "$HORIZON_ERR_LOG" 2>/dev/null || true
        if [[ -n "$WEB_USER" && "$WEB_USER" != "$APP_USER" ]] && id "$WEB_USER" &>/dev/null; then
            sudo chown "$APP_USER:$WEB_USER" "$HORIZON_LOG" "$HORIZON_ERR_LOG" 2>/dev/null || true
        else
            sudo chown "$APP_USER:$APP_USER" "$HORIZON_LOG" "$HORIZON_ERR_LOG" 2>/dev/null || true
        fi
        chmod 664 "$HORIZON_LOG" "$HORIZON_ERR_LOG" 2>/dev/null || true
    fi

    # Reload supervisor
    log_info "Reloading Supervisor..."
    sudo supervisorctl reread >/dev/null 2>&1 || { log_fail "supervisorctl reread failed"; exit 1; }
    sudo supervisorctl update >/dev/null 2>&1 || { log_fail "supervisorctl update failed"; exit 1; }

    # Start Horizon (with retry)
    HORIZON_START_ATTEMPTS=0
    while [[ $HORIZON_START_ATTEMPTS -lt 3 ]]; do
        if sudo supervisorctl start "$SUPERVISOR_PROG" 2>/dev/null; then
            break
        fi
        HORIZON_START_ATTEMPTS=$((HORIZON_START_ATTEMPTS+1))
        sleep 1
    done

    sleep 2

    # Verify Horizon is running
    if "$PHP_BIN" artisan horizon:status 2>/dev/null | grep -q "running"; then
        log_pass "Horizon is running"
    else
        HORIZON_SUP_STATUS=$(sudo supervisorctl status "$SUPERVISOR_PROG" 2>/dev/null || echo "")
        if echo "$HORIZON_SUP_STATUS" | grep -q "RUNNING"; then
            log_pass "Horizon is running (supervisor)"
        else
            log_warn "Horizon status unknown (may still be starting)"
            if [[ -n "$HORIZON_SUP_STATUS" ]]; then
                log_warn "Supervisor status: $HORIZON_SUP_STATUS"
            fi
        fi
    fi
fi

log_say ""

# =========================================================================
# 5. LARAVEL SCHEDULER (CRON)
# =========================================================================
log_say "Step 5: Setting up Laravel scheduler..."

# Determine cron user (prefer app owner for /home deployments)
APP_DIR="$(pwd -P)"
APP_USER=$(detect_app_user "$APP_DIR")
CRON_USER="$APP_USER"
if [[ -z "$CRON_USER" ]]; then
    CRON_USER=$(detect_web_user)
fi

# Idempotent cron check - use proper quoting for paths with spaces
CRON_ENTRY="* * * * * cd \"$APP_DIR\" && \"$PHP_BIN\" artisan schedule:run >> /dev/null 2>&1"

if sudo crontab -l -u "$CRON_USER" 2>/dev/null | grep -Fxq "$CRON_ENTRY"; then
    log_pass "Scheduler already configured"
else
    log_info "Adding scheduler to crontab..."
    (sudo crontab -l -u "$CRON_USER" 2>/dev/null || true; echo "$CRON_ENTRY") | sudo crontab -u "$CRON_USER" -
    log_pass "Scheduler cron added"
fi

log_say ""

# =========================================================================
# 6. LARAVEL CACHE & CONFIG
# =========================================================================
log_say "Step 6: Optimizing Laravel..."

log_info "Clearing caches..."
"$PHP_BIN" artisan config:clear >/dev/null 2>&1 || true
"$PHP_BIN" artisan cache:clear >/dev/null 2>&1 || true
"$PHP_BIN" artisan route:clear >/dev/null 2>&1 || true
"$PHP_BIN" artisan view:clear >/dev/null 2>&1 || true

log_info "Building caches..."
"$PHP_BIN" artisan config:cache >/dev/null 2>&1
"$PHP_BIN" artisan route:cache >/dev/null 2>&1
"$PHP_BIN" artisan event:cache >/dev/null 2>&1

log_pass "Laravel optimized"

log_say ""

# =========================================================================
# 7. FILE PERMISSIONS
# =========================================================================
log_say "Step 7: Setting file permissions..."

APP_DIR="$(pwd -P)"
APP_USER=$(detect_app_user "$APP_DIR")
WEB_USER=$(detect_web_user)

find storage bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
find storage -type f -name "*.log" -exec chmod 664 {} \; 2>/dev/null || true
if [[ -n "$APP_USER" ]]; then
    if [[ -n "$WEB_USER" && "$WEB_USER" != "$APP_USER" ]] && id "$WEB_USER" &>/dev/null; then
        sudo chown -R "$APP_USER:$WEB_USER" storage bootstrap/cache 2>/dev/null || true
        # Keep group write access for web server on shared hosting
        find storage bootstrap/cache -type d -exec chmod 2775 {} \; 2>/dev/null || true
    else
        sudo chown -R "$APP_USER:$APP_USER" storage bootstrap/cache 2>/dev/null || true
    fi
else
    sudo chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache 2>/dev/null || true
fi

log_pass "Permissions configured"

log_say ""

# =========================================================================
# 8. SITEMAP REFRESH (if enabled)
# =========================================================================
log_say "Step 8: Refreshing sitemaps (if enabled)..."

FEATURE_SITEMAP=$(extract_env_value "$ENV_FILE" "FEATURE_SITEMAP")
FEATURE_SITEMAP=$(echo "$FEATURE_SITEMAP" | tr '[:upper:]' '[:lower:]')

if [[ "$FEATURE_SITEMAP" == "true" || "$FEATURE_SITEMAP" == "1" ]]; then
    if "$PHP_BIN" artisan sitemap:refresh --force 2>/dev/null; then
        log_pass "Sitemap ready"
    else
        log_warn "Sitemap refresh failed (optional)"
    fi
else
    log_info "Sitemap feature not enabled - skipping"
fi

log_say ""

# =========================================================================
# FINAL VERIFICATION
# =========================================================================
log_say "============================================================"
log_say "Step 9: Final verification..."
log_say "============================================================"
log_say ""

# Test Redis
log_info "Testing Redis..."
if test_redis_connectivity "$REDIS_HOST" "$REDIS_PORT"; then
    log_pass "Redis working"
else
    log_warn "Redis test inconclusive"
fi

# Test Database
log_info "Testing database..."
TABLE_COUNT=$(MYSQL_PWD="$DB_PASSWORD" mysql -h "$DB_HOST" -u "$DB_USERNAME" "$DB_DATABASE" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_DATABASE';" 2>/dev/null | tail -1)
log_pass "Database: $TABLE_COUNT tables"

# Test Horizon (if enabled)
if [[ "$FEATURE_HORIZON" == "true" || "$FEATURE_HORIZON" == "1" ]]; then
    log_info "Testing Horizon..."
    if "$PHP_BIN" artisan horizon:status 2>/dev/null | grep -q "running"; then
        log_pass "Horizon running"
    else
        log_warn "Horizon status unknown"
    fi
fi

# Test HTTP
log_info "Testing application..."
WAIT_COUNT=0
while [[ $WAIT_COUNT -lt 30 ]]; do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 10 "$APP_URL" 2>/dev/null || echo "000")
    if [[ "$HTTP_CODE" == "200" ]] || [[ "$HTTP_CODE" == "302" ]]; then
        log_pass "Application responding (HTTP $HTTP_CODE)"
        break
    fi
    WAIT_COUNT=$((WAIT_COUNT+1))
    sleep 1
done

if [[ $WAIT_COUNT -eq 30 ]]; then
    log_warn "Application not responding yet (may still be warming up)"
fi

log_say ""
log_say "============================================================"
log_say "✅ $ENVIRONMENT SETUP COMPLETE!"
log_say "============================================================"
log_say ""
log_say "Next: Run ./scripts/vps-verify.sh to verify everything"
log_say ""

# Clear sensitive variables from memory
unset DB_PASSWORD

exit 0
