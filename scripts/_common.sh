#!/bin/bash
# ============================================================================
# Laravel Starter Scripts - Common Functions Library
# ============================================================================
# Shared functions for VPS setup, Horizon configuration, and validation scripts.
# This file should be sourced by other scripts in this directory.
#
# Usage:
#   source scripts/_common.sh
#   detect_environment    # Sets: ENVIRONMENT, SUPERVISOR_PROG, ENV_FILE
#   detect_php_binary     # Returns: PHP binary path
#   get_app_name_slug     # Returns: app name derived from .env APP_NAME
#   test_redis_connectivity REDIS_HOST REDIS_PORT
#   count_processes pattern
#   log_pass "message"
#   log_fail "message"
#   log_warn "message"
#   log_info "message"
# ============================================================================

# ============================================================================
# COLORS & LOGGING
# ============================================================================

readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m' # No Color

log_pass() { echo -e "${GREEN}✅ $*${NC}"; }
log_fail() { echo -e "${RED}❌ $*${NC}"; }
log_warn() { echo -e "${YELLOW}⚠️  $*${NC}"; }
log_info() { echo -e "${BLUE}ℹ️  $*${NC}"; }
log_say()  { echo -e "${NC}$*"; }

# ============================================================================
# EXTRACT ENV VALUE
# ============================================================================
# Safely extracts a value from .env file
# Usage: extract_env_value ENV_FILE KEY_NAME
# Returns: the value (empty if not found)
extract_env_value() {
    local env_file="$1"
    local key="$2"

    grep "^${key}=" "$env_file" 2>/dev/null | cut -d'=' -f2 | sed 's/#.*//' | tr -d '"' | tr -d "'" | xargs
}

# ============================================================================
# APP NAME DERIVATION (DYNAMIC)
# ============================================================================
# Derives app name slug from .env APP_NAME value
# Converts to lowercase, replaces spaces with dashes, strips special chars
# Usage: get_app_name_slug [ENV_FILE]
# Returns: slugified app name (e.g., "My Cool App" -> "my-cool-app")
get_app_name_slug() {
    local env_file="${1:-.env}"
    local raw_name=""

    if [[ -f "$env_file" ]]; then
        raw_name=$(extract_env_value "$env_file" "APP_NAME")
    fi

    if [[ -z "$raw_name" ]]; then
        # Fallback: try to derive from current directory name
        raw_name=$(basename "$(pwd -P)")
    fi

    # Transform to slug:
    # 1. Convert to lowercase
    # 2. Replace spaces with dashes
    # 3. Remove special chars except alphanumeric and dashes
    # 4. Collapse multiple dashes
    # 5. Trim leading/trailing dashes
    echo "$raw_name" | \
        tr '[:upper:]' '[:lower:]' | \
        sed 's/ /-/g' | \
        sed 's/[^a-z0-9-]//g' | \
        sed 's/--*/-/g' | \
        sed 's/^-//' | \
        sed 's/-$//'
}

# ============================================================================
# PHP BINARY DETECTION (DRY)
# ============================================================================
# Detects PHP binary with fallback chain:
# 1. cPanel EA-PHP 8.4
# 2. Homebrew PHP (Apple Silicon)
# 3. Homebrew PHP (Intel)
# 4. Laravel Herd (macOS)
# 5. System PHP
# Returns: PHP binary path or empty string if not found
detect_php_binary() {
    # cPanel PHP 8.4 (Linux VPS)
    if [[ -x "/opt/cpanel/ea-php84/root/usr/bin/php" ]]; then
        echo "/opt/cpanel/ea-php84/root/usr/bin/php"
        return 0
    fi

    # Homebrew PHP 8.4 (macOS Apple Silicon)
    if [[ -f "/opt/homebrew/bin/php" ]]; then
        echo "/opt/homebrew/bin/php"
        return 0
    fi

    # Homebrew PHP (Intel Mac)
    if [[ -f "/usr/local/bin/php" ]]; then
        echo "/usr/local/bin/php"
        return 0
    fi

    # Laravel Herd (macOS)
    if [[ -f "$HOME/Library/Application Support/Herd/bin/php" ]]; then
        echo "$HOME/Library/Application Support/Herd/bin/php"
        return 0
    fi

    # System PHP
    if command -v php &>/dev/null; then
        echo "$(command -v php)"
        return 0
    fi

    # Not found
    echo ""
    return 1
}

# ============================================================================
# ENVIRONMENT DETECTION (DRY)
# ============================================================================
# Auto-detects environment from folder path and .env file
# Sets global variables: ENVIRONMENT, SUPERVISOR_PROG, ENV_FILE, APP_NAME_SLUG
# Returns: 0 if detected, 1 if failed
detect_environment() {
    local current_path="$(pwd)"
    local app_name_slug=""

    # Try to get app name slug from appropriate .env file
    if [[ -f ".env.production" ]] && [[ "$current_path" == */production ]]; then
        app_name_slug=$(get_app_name_slug ".env.production")
        ENVIRONMENT="production"
        ENV_FILE=".env.production"
        SUPERVISOR_PROG="${app_name_slug}-production-horizon"
        APP_NAME_SLUG="$app_name_slug"
        return 0
    fi

    if [[ -f ".env.preview" ]] && [[ "$current_path" == */preview ]]; then
        app_name_slug=$(get_app_name_slug ".env.preview")
        ENVIRONMENT="preview"
        ENV_FILE=".env.preview"
        SUPERVISOR_PROG="${app_name_slug}-preview-horizon"
        APP_NAME_SLUG="$app_name_slug"
        return 0
    fi

    # Fallback: try .env if no environment detected
    if [[ -f ".env" ]]; then
        app_name_slug=$(get_app_name_slug ".env")
        ENVIRONMENT="${APP_ENV:-local}"
        ENV_FILE=".env"
        SUPERVISOR_PROG="${app_name_slug}-horizon"
        APP_NAME_SLUG="$app_name_slug"
        return 0
    fi

    return 1
}

# ============================================================================
# FEATURE FLAG HELPERS
# ============================================================================
# Check if a feature is enabled in .env
# Usage: is_feature_enabled FEATURE_NAME [ENV_FILE]
# Returns: 0 if enabled (true/1/yes), 1 otherwise
is_feature_enabled() {
    local feature_name="$1"
    local env_file="${2:-.env}"
    local value=""

    value=$(extract_env_value "$env_file" "$feature_name")
    value=$(echo "$value" | tr '[:upper:]' '[:lower:]')

    if [[ "$value" == "true" ]] || [[ "$value" == "1" ]] || [[ "$value" == "yes" ]]; then
        return 0
    fi
    return 1
}

# ============================================================================
# REDIS CONNECTIVITY TEST (DRY)
# ============================================================================
# Tests Redis connectivity with timeout
# Usage: test_redis_connectivity REDIS_HOST REDIS_PORT [TIMEOUT]
# Returns: 0 if PONG, 1 if failed
test_redis_connectivity() {
    local redis_host="${1:-127.0.0.1}"
    local redis_port="${2:-6379}"
    local timeout="${3:-2}"

    if ! command -v redis-cli &>/dev/null; then
        log_warn "redis-cli not installed"
        return 1
    fi

    local result=""
    if command -v timeout &>/dev/null; then
        result=$(timeout "$timeout" redis-cli -h "$redis_host" -p "$redis_port" ping 2>&1 || echo "FAILED")
    else
        result=$(redis-cli -h "$redis_host" -p "$redis_port" ping 2>&1 || echo "FAILED")
    fi

    if [[ "$result" == "PONG" ]]; then
        return 0
    else
        log_warn "Redis test returned: $result"
        return 1
    fi
}

# ============================================================================
# PROCESS COUNTING (DRY & RELIABLE)
# ============================================================================
# Counts processes matching pattern using pgrep (reliable)
# Usage: count_processes pattern
# Returns: number of processes matching pattern
count_processes() {
    local pattern="$1"
    local count
    count=$(pgrep -c "$pattern" 2>/dev/null || true)
    if [[ -z "$count" ]]; then
        count=0
    fi
    echo "$count"
}

# ============================================================================
# MYSQL CONNECTIVITY TEST
# ============================================================================
# Tests MySQL connectivity with credentials from .env
# Usage: test_mysql_connectivity ENV_FILE
# Returns: 0 if connected, 1 if failed
# NOTE: Uses MYSQL_PWD env var to avoid password exposure in ps output
test_mysql_connectivity() {
    local env_file="${1:-.env}"
    local db_host db_database db_username db_password

    # Extract values from .env (safely)
    db_host=$(extract_env_value "$env_file" "DB_HOST")
    db_database=$(extract_env_value "$env_file" "DB_DATABASE")
    db_username=$(extract_env_value "$env_file" "DB_USERNAME")
    db_password=$(extract_env_value "$env_file" "DB_PASSWORD")

    # Test connection using MYSQL_PWD env var (not visible in ps output)
    if MYSQL_PWD="$db_password" mysql -h "$db_host" -u "$db_username" "$db_database" -e "SELECT 1;" 2>/dev/null; then
        return 0
    else
        return 1
    fi
}

# ============================================================================
# CHECK SUDO ACCESS
# ============================================================================
# Validates that the script can use sudo without password
# Returns: 0 if sudo available, 1 if not
check_sudo_access() {
    if sudo -n true 2>/dev/null; then
        return 0
    else
        log_warn "This script needs sudo access. Please enter your password:"
        sudo -v 2>/dev/null
        return $?
    fi
}

# ============================================================================
# DETECT APP USER
# ============================================================================
# Determines the app owner for home-directory deployments (cPanel/preview).
# Returns: username via stdout (empty if not detected)
detect_app_user() {
    local app_dir="${1:-$(pwd -P)}"
    local owner=""

    if [[ "$app_dir" == /home/* ]] || [[ "$app_dir" == /Users/* ]]; then
        owner=$(stat -c '%U' "$app_dir" 2>/dev/null || stat -f '%Su' "$app_dir" 2>/dev/null || echo "")
        if [[ -n "$owner" && "$owner" != "root" && "$owner" != "UNKNOWN" ]]; then
            echo "$owner"
            return 0
        fi
    fi

    echo ""
    return 1
}

# ============================================================================
# DETECT WEB USER
# ============================================================================
# Determines the web server user (www-data, apache, nginx, nobody)
# Returns: username via stdout
detect_web_user() {
    local user="www-data"

    if id "$user" &>/dev/null; then
        echo "$user"
        return 0
    fi

    if id "apache" &>/dev/null; then
        echo "apache"
        return 0
    fi

    if id "nginx" &>/dev/null; then
        echo "nginx"
        return 0
    fi

    echo "nobody"
    return 1
}

# ============================================================================
# DETECT OS TYPE
# ============================================================================
# Returns: "macos", "linux", or "unknown"
detect_os() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        echo "macos"
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        echo "linux"
    else
        echo "unknown"
    fi
}

# ============================================================================
# DETECT SUPERVISOR CONF DIR
# ============================================================================
# Determines the active supervisord include directory
# Returns: directory path via stdout (default /etc/supervisord.d)
detect_supervisor_dir() {
    local conf="/etc/supervisord.conf"
    local pattern=""

    if [[ -f "$conf" ]]; then
        pattern=$(awk -F= '/^[[:space:]]*files[[:space:]]*=/{print $2; exit}' "$conf" | tr -d '"' | xargs)
        if [[ -n "$pattern" ]]; then
            if [[ "$pattern" != /* ]]; then
                pattern="/etc/${pattern}"
            fi
            echo "$(dirname "$pattern")"
            return 0
        fi
    fi

    if [[ -d "/etc/supervisord.d" ]]; then
        echo "/etc/supervisord.d"
        return 0
    fi

    if [[ -d "/etc/supervisor/conf.d" ]]; then
        echo "/etc/supervisor/conf.d"
        return 0
    fi

    echo "/etc/supervisord.d"
    return 0
}

# ============================================================================
# DETECT SUPERVISOR CONF EXTENSION
# ============================================================================
# Returns: "ini" or "conf" based on supervisord include pattern
detect_supervisor_ext() {
    local conf="/etc/supervisord.conf"
    local pattern=""
    local ext=""

    if [[ -f "$conf" ]]; then
        pattern=$(awk -F= '/^[[:space:]]*files[[:space:]]*=/{print $2; exit}' "$conf" | tr -d '"' | xargs)
        if [[ -n "$pattern" ]]; then
            ext=$(echo "$pattern" | sed -n 's/.*\*\.//p' | tr -d ' ' | tr -d '\r')
        fi
    fi

    if [[ -n "$ext" ]]; then
        echo "$ext"
        return 0
    fi

    if [[ -d "/etc/supervisor/conf.d" ]]; then
        echo "conf"
        return 0
    fi

    echo "ini"
    return 0
}

# ============================================================================
# ENSURE DIRECTORY EXISTS
# ============================================================================
# Creates directory with optional owner/permissions
# Usage: ensure_directory /path/to/dir [owner:group] [mode]
ensure_directory() {
    local dir="$1"
    local owner="${2:-}"
    local mode="${3:-}"

    if [[ ! -d "$dir" ]]; then
        mkdir -p "$dir" || return 1
    fi

    if [[ -n "$owner" ]]; then
        chown "$owner" "$dir" 2>/dev/null || true
    fi

    if [[ -n "$mode" ]]; then
        chmod "$mode" "$dir" || return 1
    fi

    return 0
}

# ============================================================================
# VALIDATE FILE PERMISSIONS
# ============================================================================
# Checks if file has secure permissions (not world-readable)
# Usage: is_file_secure /path/to/.env
# Returns: 0 if secure (permissions ≤ 640), 1 if world-readable
is_file_secure() {
    local file="$1"
    local perms mode

    if [[ ! -f "$file" ]]; then
        return 0  # File doesn't exist, can't be world-readable
    fi

    # Get permissions (works on both Linux and macOS)
    if ! command -v stat &>/dev/null; then
        return 1  # Can't verify, assume not secure
    fi

    if [[ "$(uname)" == "Darwin" ]]; then
        mode=$(stat -f "%OLp" "$file")
    else
        mode=$(stat -c "%a" "$file")
    fi

    # Check if world-readable (last digit > 4)
    if [[ ${mode: -1} -gt 4 ]]; then
        return 1  # World-readable, not secure
    fi

    return 0  # Secure
}

# ============================================================================
# REPLACE CONFIG PLACEHOLDER
# ============================================================================
# Safely replaces placeholder text in a file
# Usage: replace_placeholder FILE OLD_PLACEHOLDER NEW_VALUE
replace_placeholder() {
    local file="$1"
    local placeholder="$2"
    local value="$3"

    # Use | as delimiter in sed to avoid issues with paths
    sed -i.bak "s|${placeholder}|${value}|g" "$file"
    # Clean up backup file
    rm -f "${file}.bak"
}

# ============================================================================
# PRINT HEADER
# ============================================================================
# Prints a formatted header for script sections
print_header() {
    local title="$1"
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  ${title}${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
}

# ============================================================================
# PRINT SECTION
# ============================================================================
# Prints a formatted section header
print_section() {
    local title="$1"
    echo ""
    echo -e "${BLUE}🔹 ${title}${NC}"
}

# ============================================================================
# Export all functions so they're available when sourced
# ============================================================================
export -f log_pass log_fail log_warn log_info log_say
export -f extract_env_value get_app_name_slug is_feature_enabled
export -f detect_php_binary detect_environment
export -f test_redis_connectivity test_mysql_connectivity
export -f count_processes check_sudo_access detect_app_user detect_web_user detect_os
export -f detect_supervisor_dir detect_supervisor_ext
export -f ensure_directory is_file_secure replace_placeholder
export -f print_header print_section
