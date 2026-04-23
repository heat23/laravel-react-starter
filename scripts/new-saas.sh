#!/usr/bin/env bash
# new-saas.sh — Bootstrap a new SaaS product from the laravel-react-starter template.
#
# Usage:
#   bash scripts/new-saas.sh --target-dir <path> --name <"Product Name"> --domain <yourdomain.com>
#
# Optional:
#   --template <path|url>   Git clone source (default: this repo's root)
#
# What it does:
#   1. Shallow-clone the template into --target-dir
#   2. Slug-replace "laravel-react-starter" across package.json, composer.json, README, CI
#   3. Substitute {{APP_NAME}} and {{APP_DOMAIN}} in .env.example → .env
#   4. composer install && npm install && npm run build
#   5. php artisan key:generate
#   6. Fresh git history (orphan first commit)
#   7. Prints the residual checklist from docs/FORKING.md

set -euo pipefail

# ── helpers ────────────────────────────────────────────────────────────────────

die()  { echo "ERROR: $*" >&2; exit 1; }
info() { echo "▸ $*"; }
ok()   { echo "✓ $*"; }

# Escape a string for use in the *replacement* side of a sed s|...|...|g command.
# Escapes: backslash first, then & (matched-text metachar), then | (our delimiter).
escape_for_sed() { printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/|/\\|/g'; }

# ── argument parsing ───────────────────────────────────────────────────────────

TARGET_DIR=""
APP_NAME=""
DOMAIN=""
TEMPLATE_SRC=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        --target-dir) TARGET_DIR="$2"; shift 2 ;;
        --name)       APP_NAME="$2";   shift 2 ;;
        --domain)     DOMAIN="$2";     shift 2 ;;
        --template)   TEMPLATE_SRC="$2"; shift 2 ;;
        *) die "Unknown argument: $1" ;;
    esac
done

[[ -n "$TARGET_DIR" ]] || die "--target-dir is required"
[[ -n "$APP_NAME"   ]] || die "--name is required"
[[ -n "$DOMAIN"     ]] || die "--domain is required"

# Default template: directory containing this script's parent (the repo root)
if [[ -z "$TEMPLATE_SRC" ]]; then
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    TEMPLATE_SRC="$(cd "$SCRIPT_DIR/.." && pwd)"
fi

# Derive slug: lowercase, spaces → hyphens, strip non-alphanumeric except hyphens
SLUG="$(echo "$APP_NAME" | tr '[:upper:]' '[:lower:]' | tr ' ' '-' | sed 's/[^a-z0-9-]//g')"
[[ -n "$SLUG" ]] || die "--name '$APP_NAME' produced an empty slug (contains only special characters)"

info "Template : $TEMPLATE_SRC"
info "Target   : $TARGET_DIR"
info "Name     : $APP_NAME"
info "Slug     : $SLUG"
info "Domain   : $DOMAIN"
echo ""

# ── 1. Clone ──────────────────────────────────────────────────────────────────

[[ -e "$TARGET_DIR" ]] && die "Target directory already exists: $TARGET_DIR"

info "Cloning template..."
git clone --depth=1 "$TEMPLATE_SRC" "$TARGET_DIR"
ok "Cloned into $TARGET_DIR"

cd "$TARGET_DIR"

# Remove the origin remote — this is now a fresh project
git remote remove origin 2>/dev/null || true

# ── 2. Slug replacement ────────────────────────────────────────────────────────

info "Replacing 'laravel-react-starter' → '$SLUG'..."

replace_in_file() {
    local file="$1"
    [[ -f "$file" ]] || return 0
    if [[ "$(uname)" == "Darwin" ]]; then
        sed -i '' "s|laravel-react-starter|${SLUG}|g" "$file"
    else
        sed -i "s|laravel-react-starter|${SLUG}|g" "$file"
    fi
}

replace_in_file "package.json"
replace_in_file "composer.json"
replace_in_file "README.md"

# CI workflows
for yml in .github/workflows/*.yml; do
    replace_in_file "$yml"
done

ok "Slug replaced"

# ── 3. Generate .env ──────────────────────────────────────────────────────────

info "Generating .env from .env.example..."

[[ -f ".env.example" ]] || die ".env.example not found in $TARGET_DIR"

# Escape values for sed replacement (handles &, \, | in app name / domain).
# No -i flag here: we read from .env.example and redirect to .env, so
# macOS vs GNU sed differences don't apply to this step.
APP_NAME_SAFE="$(escape_for_sed "$APP_NAME")"
DOMAIN_SAFE="$(escape_for_sed "$DOMAIN")"

sed -e "s|{{APP_NAME}}|${APP_NAME_SAFE}|g" \
    -e "s|{{APP_DOMAIN}}|${DOMAIN_SAFE}|g" \
    .env.example > .env

# Set APP_URL to the domain
if grep -q "^APP_URL=" .env; then
    if [[ "$(uname)" == "Darwin" ]]; then
        sed -i '' "s|^APP_URL=.*|APP_URL=http://localhost|" .env
    else
        sed -i "s|^APP_URL=.*|APP_URL=http://localhost|" .env
    fi
fi

ok ".env generated"

# ── 4. Install dependencies & build ──────────────────────────────────────────

info "Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1 | tail -3
ok "composer install done"

# Key must be set before artisan commands that boot the app (ziggy:generate)
info "Generating application key..."
php artisan key:generate --ansi
ok "App key set"

# ziggy.js is gitignored but required by resources/js/bootstrap.ts at build time
info "Generating Ziggy route file..."
php artisan ziggy:generate 2>&1 | tail -2
ok "ziggy.js generated"

info "Running npm install..."
npm install --silent 2>&1 | tail -3
ok "npm install done"

info "Running npm run build..."
npm run build 2>&1 | tail -5
ok "npm build done"

# ── 6. Fresh git history ──────────────────────────────────────────────────────

info "Initialising fresh git history..."

# Remove the old .git and start clean
rm -rf .git
git init
git add -A
git commit -m "chore: initial commit from laravel-react-starter template

Bootstrapped with scripts/new-saas.sh.
Product: ${APP_NAME}
Domain:  ${DOMAIN}" --no-verify

ok "Fresh git history created (1 orphan commit)"

# ── 7. Residual checklist ─────────────────────────────────────────────────────

echo ""
echo "════════════════════════════════════════════════════════"
echo "  ✅  $APP_NAME is ready at: $TARGET_DIR"
echo "════════════════════════════════════════════════════════"
echo ""
echo "NEXT STEPS — human decisions required (see docs/FORKING.md):"
echo ""
echo "  1. DECIDE feature flags to enable in config/features.php"
echo "     Recommended: billing.enabled, api_tokens.enabled, two_factor.enabled"
echo ""
echo "  2. DELETE marketing-only content (this starter's own marketing pages):"
echo "     rm resources/js/Pages/Public/Guides/BuildVsBuyGuide.tsx"
echo "     rm resources/js/Pages/Public/Guides/LaravelSaasGuide.tsx"
echo "     rm resources/js/Pages/Public/Guides/SaasStarterKitComparison.tsx"
echo "     rm resources/js/Pages/Public/Guides/StripeBillingGuide.tsx"
echo "     rm resources/js/Pages/Public/Guides/TenancyArchitectureGuide.tsx"
echo "     rm resources/js/Pages/Public/Guides/WebhookGuide.tsx"
echo "     rm -rf resources/js/Pages/Public/Compare/"
echo "     (Then remove matching routes from routes/web.php)"
echo ""
echo "  3. SET UP STRIPE (if billing.enabled):"
echo "     - Create Products/Prices matching PlanTier enum"
echo "     - Create webhook endpoint → https://www.$DOMAIN/stripe/webhook"
echo "     - Copy STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET to .env"
echo "     ⚠️  Do NOT enable FEATURE_BILLING_TAX without a compliance review"
echo ""
echo "  4. CONFIGURE production .env:"
echo "     APP_ENV=production  APP_DEBUG=false  APP_URL=https://www.$DOMAIN"
echo "     SESSION_ENCRYPT=true  SESSION_SECURE_COOKIE=true"
echo ""
echo "  5. PROVISION VPS:"
echo "     bash scripts/vps-setup.sh    # initial server setup"
echo "     php artisan migrate --force"
echo "     php artisan optimize"
echo "     bash scripts/vps-verify.sh   # post-deploy checks"
echo ""
echo "  Full checklist: docs/FORKING.md"
echo ""
