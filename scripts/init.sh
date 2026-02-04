#!/usr/bin/env bash

# ============================================================================
# Laravel React Starter - Post-Clone Initialization Script
# ============================================================================
#
# This script configures a new project from the starter template.
# Run once after cloning: ./scripts/init.sh
#
# It will:
#   1. Prompt for project configuration
#   2. Replace all {{PLACEHOLDERS}} with your values
#   3. Configure feature flags
#   4. Install dependencies
#   5. Generate application key
#   6. Initialize git repository
#   7. Clean up template files
#
# ============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# Utility functions
print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BOLD}${CYAN}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

print_step() {
    echo -e "${GREEN}▸${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${CYAN}ℹ${NC} $1"
}

# Prompt with default value
prompt() {
    local var_name=$1
    local prompt_text=$2
    local default=$3
    local validation=$4
    local value

    while true; do
        if [ -n "$default" ]; then
            echo -ne "${BOLD}$prompt_text${NC} [${CYAN}$default${NC}]: "
        else
            echo -ne "${BOLD}$prompt_text${NC}: "
        fi
        read -r value
        value=${value:-$default}

        if [ -z "$value" ]; then
            print_error "This field is required."
            continue
        fi

        if [ -n "$validation" ]; then
            if ! echo "$value" | grep -qE "$validation"; then
                print_error "Invalid format. Expected: $validation"
                continue
            fi
        fi

        eval "$var_name='$value'"
        break
    done
}

# Prompt yes/no with default
prompt_yn() {
    local var_name=$1
    local prompt_text=$2
    local default=$3
    local value

    if [ "$default" = "y" ]; then
        echo -ne "${BOLD}$prompt_text${NC} [${CYAN}Y/n${NC}]: "
    else
        echo -ne "${BOLD}$prompt_text${NC} [${CYAN}y/N${NC}]: "
    fi
    read -r value
    value=${value:-$default}
    value=$(echo "$value" | tr '[:upper:]' '[:lower:]')

    if [ "$value" = "y" ] || [ "$value" = "yes" ]; then
        eval "$var_name=true"
    else
        eval "$var_name=false"
    fi
}

# Convert to kebab-case
to_kebab_case() {
    echo "$1" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g' | sed 's/--*/-/g' | sed 's/^-//' | sed 's/-$//'
}

# Cross-platform sed in-place edit (macOS vs Linux)
sed_inplace() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "$@"
    else
        sed -i "$@"
    fi
}

# Replace placeholder in all files
replace_placeholder() {
    local placeholder=$1
    local value=$2
    local escaped_value

    # Escape special characters for sed (more comprehensive escaping)
    escaped_value=$(printf '%s\n' "$value" | sed -e 's/[\/&\$]/\\&/g' | sed -e 's/[]\[]/\\&/g')

    # Find and replace in all relevant files
    find . -type f \( \
        -name "*.php" -o \
        -name "*.json" -o \
        -name "*.ts" -o \
        -name "*.tsx" -o \
        -name "*.js" -o \
        -name "*.env*" -o \
        -name "*.md" -o \
        -name "*.yml" -o \
        -name "*.yaml" -o \
        -name "*.xml" \
    \) ! -path "./vendor/*" ! -path "./node_modules/*" ! -path "./.git/*" \
    -print0 | while IFS= read -r -d '' file; do
        sed_inplace "s/${placeholder}/${escaped_value}/g" "$file" 2>/dev/null || true
    done
}

# Update .env feature flags
set_feature_flag() {
    local flag=$1
    local value=$2
    local file=".env"

    if [ -f "$file" ]; then
        if grep -q "^${flag}=" "$file"; then
            sed_inplace "s/^${flag}=.*/${flag}=${value}/" "$file"
        else
            echo "${flag}=${value}" >> "$file"
        fi
    fi
}

# ============================================================================
# Main Script
# ============================================================================

print_header "Laravel React Starter - Project Initialization"

# Check if we're in the right directory
if [ ! -f "composer.json" ] || [ ! -f "template.json" ]; then
    print_error "This script must be run from the template root directory."
    print_info "Make sure you're in the directory containing composer.json and template.json"
    exit 1
fi

# Check if already initialized
if [ -f ".initialized" ]; then
    print_warning "This project appears to already be initialized."
    echo -ne "Continue anyway? [y/N]: "
    read -r continue_anyway
    if [ "$continue_anyway" != "y" ] && [ "$continue_anyway" != "Y" ]; then
        echo "Aborted."
        exit 0
    fi
fi

echo "This wizard will configure your new Laravel React application."
echo "Press Enter to accept defaults shown in [brackets]."
echo ""

# ============================================================================
# Step 1: Project Information
# ============================================================================

print_header "Step 1/4: Project Information"

prompt APP_NAME "Application name (e.g., 'My Awesome App')" "Laravel App" "^[a-zA-Z0-9 ]+$"
prompt PROJECT_NAME "Project slug (kebab-case, e.g., 'my-awesome-app')" "$(to_kebab_case "$APP_NAME")" "^[a-z0-9-]+$"
prompt VENDOR_NAME "Vendor/organization name (e.g., 'acme')" "acme" "^[a-z0-9-]+$"
prompt APP_DOMAIN "Domain name (e.g., 'example.com')" "${PROJECT_NAME}.com" "^[a-z0-9.-]+$"
prompt APP_DESCRIPTION "Short description" "A modern Laravel application" ""

# ============================================================================
# Step 2: Feature Flags
# ============================================================================

print_header "Step 2/4: Feature Flags"

echo "Select which optional features to enable:"
echo ""

prompt_yn FEATURE_BILLING "Enable billing/subscriptions (Stripe)?" "n"
prompt_yn FEATURE_SOCIAL_AUTH "Enable social authentication (Google/GitHub)?" "n"
prompt_yn FEATURE_EMAIL_VERIFICATION "Require email verification?" "y"
prompt_yn FEATURE_API_TOKENS "Enable API token management?" "y"

echo ""
echo -e "${BOLD}Monitoring & Analytics:${NC}"
prompt_yn ENABLE_SENTRY "Enable Sentry error tracking?" "y"

# ============================================================================
# Step 3: Confirmation
# ============================================================================

print_header "Step 3/4: Confirmation"

echo "Please review your configuration:"
echo ""
echo -e "  ${BOLD}Application Name:${NC}    $APP_NAME"
echo -e "  ${BOLD}Project Slug:${NC}        $PROJECT_NAME"
echo -e "  ${BOLD}Vendor:${NC}              $VENDOR_NAME"
echo -e "  ${BOLD}Domain:${NC}              $APP_DOMAIN"
echo -e "  ${BOLD}Description:${NC}         $APP_DESCRIPTION"
echo ""
echo -e "  ${BOLD}Features:${NC}"
echo -e "    Billing:           $([ "$FEATURE_BILLING" = "true" ] && echo -e "${GREEN}enabled${NC}" || echo -e "${YELLOW}disabled${NC}")"
echo -e "    Social Auth:       $([ "$FEATURE_SOCIAL_AUTH" = "true" ] && echo -e "${GREEN}enabled${NC}" || echo -e "${YELLOW}disabled${NC}")"
echo -e "    Email Verification:$([ "$FEATURE_EMAIL_VERIFICATION" = "true" ] && echo -e "${GREEN}enabled${NC}" || echo -e "${YELLOW}disabled${NC}")"
echo -e "    API Tokens:        $([ "$FEATURE_API_TOKENS" = "true" ] && echo -e "${GREEN}enabled${NC}" || echo -e "${YELLOW}disabled${NC}")"
echo -e "    Sentry:            $([ "$ENABLE_SENTRY" = "true" ] && echo -e "${GREEN}enabled${NC}" || echo -e "${YELLOW}disabled${NC}")"
echo ""

echo -ne "${BOLD}Proceed with initialization?${NC} [Y/n]: "
read -r confirm
confirm=${confirm:-y}

if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
    echo "Aborted."
    exit 0
fi

# ============================================================================
# Step 4: Initialization
# ============================================================================

print_header "Step 4/4: Initializing Project"

# Replace placeholders
print_step "Replacing placeholders..."
replace_placeholder "{{APP_NAME}}" "$APP_NAME"
replace_placeholder "{{PROJECT_NAME}}" "$PROJECT_NAME"
replace_placeholder "{{VENDOR_NAME}}" "$VENDOR_NAME"
replace_placeholder "{{APP_DOMAIN}}" "$APP_DOMAIN"
replace_placeholder "{{APP_DESCRIPTION}}" "$APP_DESCRIPTION"
replace_placeholder "{{TERMS_URL}}" "/terms"
replace_placeholder "{{PRIVACY_URL}}" "/privacy"
print_success "Placeholders replaced"

# Copy .env.example to .env
print_step "Creating .env file..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    print_success ".env created from .env.example"
else
    print_warning ".env already exists, skipping"
fi

# Set feature flags
print_step "Configuring feature flags..."
set_feature_flag "FEATURE_BILLING" "$FEATURE_BILLING"
set_feature_flag "FEATURE_SOCIAL_AUTH" "$FEATURE_SOCIAL_AUTH"
set_feature_flag "FEATURE_EMAIL_VERIFICATION" "$FEATURE_EMAIL_VERIFICATION"
set_feature_flag "FEATURE_API_TOKENS" "$FEATURE_API_TOKENS"
print_success "Feature flags configured"

# Install PHP dependencies
print_step "Installing PHP dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-interaction --prefer-dist --quiet
    print_success "Composer dependencies installed"
else
    print_warning "Composer not found. Run 'composer install' manually."
fi

# Install optional packages based on features
if [ "$FEATURE_BILLING" = "true" ]; then
    print_step "Installing Stripe/Cashier for billing..."
    if command -v composer &> /dev/null; then
        composer require laravel/cashier --no-interaction --quiet
        print_success "Laravel Cashier installed"
    fi
fi

if [ "$FEATURE_SOCIAL_AUTH" = "true" ]; then
    print_step "Installing Socialite for social auth..."
    if command -v composer &> /dev/null; then
        composer require laravel/socialite --no-interaction --quiet
        print_success "Laravel Socialite installed"
    fi
fi

if [ "$ENABLE_SENTRY" = "true" ]; then
    print_step "Installing Sentry for error tracking..."
    if command -v composer &> /dev/null; then
        composer require sentry/sentry-laravel --no-interaction --quiet
        print_success "Sentry Laravel SDK installed"
    fi
fi

# Install Node dependencies
print_step "Installing Node dependencies..."
if command -v npm &> /dev/null; then
    npm install --silent 2>/dev/null
    print_success "NPM dependencies installed"
else
    print_warning "NPM not found. Run 'npm install' manually."
fi

# Generate application key
print_step "Generating application key..."
if command -v php &> /dev/null && [ -f "artisan" ]; then
    php artisan key:generate --quiet
    print_success "Application key generated"
else
    print_warning "PHP not found. Run 'php artisan key:generate' manually."
fi

# Create SQLite database file
print_step "Creating database..."
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
    print_success "SQLite database created"
else
    print_warning "Database already exists, skipping"
fi

# Run migrations
print_step "Running migrations..."
if command -v php &> /dev/null && [ -f "artisan" ]; then
    php artisan migrate --force --quiet
    print_success "Database migrations completed"
else
    print_warning "Run 'php artisan migrate' manually."
fi

# Generate Ziggy routes
print_step "Generating Ziggy routes..."
if command -v php &> /dev/null && [ -f "artisan" ]; then
    php artisan ziggy:generate --quiet 2>/dev/null || true
    print_success "Ziggy routes generated"
else
    print_warning "Run 'php artisan ziggy:generate' manually."
fi

# Cleanup template files
print_step "Cleaning up template files..."
rm -f template.json 2>/dev/null || true
rm -f TEMPLATE_README.md 2>/dev/null || true
print_success "Template files removed"

# Initialize git repository
print_step "Initializing git repository..."
if [ -d ".git" ]; then
    print_warning "Git repository already exists, skipping init"
else
    git init --quiet
    print_success "Git repository initialized"
fi

# Create initial commit
print_step "Creating initial commit..."
git add -A
git commit -m "Initial commit: $APP_NAME

Initialized from Laravel React Starter template.

Features enabled:
- Billing: $FEATURE_BILLING
- Social Auth: $FEATURE_SOCIAL_AUTH
- Email Verification: $FEATURE_EMAIL_VERIFICATION
- API Tokens: $FEATURE_API_TOKENS" --quiet 2>/dev/null || print_warning "Nothing to commit or git config needed"

# Remove init script (self-destruct)
print_step "Removing initialization script..."
rm -f scripts/init.sh 2>/dev/null || true
rmdir scripts 2>/dev/null || true
print_success "Initialization script removed"

# Mark as initialized
touch .initialized

# ============================================================================
# Complete
# ============================================================================

print_header "Initialization Complete!"

echo -e "${GREEN}Your project '$APP_NAME' is ready!${NC}"
echo ""
echo "Next steps:"
echo ""
echo -e "  ${BOLD}1. Start development server:${NC}"
echo "     composer dev"
echo ""
echo -e "  ${BOLD}2. Open in browser:${NC}"
echo "     http://${APP_DOMAIN}.test"
echo ""

if [ "$FEATURE_BILLING" = "true" ]; then
    echo -e "  ${BOLD}3. Configure Stripe (billing enabled):${NC}"
    echo "     Add STRIPE_KEY and STRIPE_SECRET to .env"
    echo "     Get keys from: https://dashboard.stripe.com/apikeys"
    echo ""
fi

if [ "$FEATURE_SOCIAL_AUTH" = "true" ]; then
    echo -e "  ${BOLD}4. Configure OAuth (social auth enabled):${NC}"
    echo "     Add GOOGLE_CLIENT_ID/SECRET or GITHUB_CLIENT_ID/SECRET to .env"
    echo ""
fi

echo -e "  ${BOLD}Documentation:${NC}"
echo "     https://laravel.com/docs"
echo "     https://inertiajs.com"
echo "     https://react.dev"
echo ""
echo -e "${GREEN}Happy coding!${NC}"
echo ""
