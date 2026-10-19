#!/usr/bin/env bash
# Prepare a fresh checkout of this repository so the site can be started with
# `.devcontainer/serve.sh`. Written to be safe to run more than once: it never
# overwrites an existing .env and never re-seeds a database that has users.
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

step() { printf '[beloved] %s\n' "$*"; }
fail() { printf '[beloved] %s\n' "$*" >&2; exit 1; }

command -v php > /dev/null 2>&1 || fail "PHP is not on PATH."
command -v composer > /dev/null 2>&1 || fail "Composer is not on PATH."
command -v node > /dev/null 2>&1 || fail "Node.js is not on PATH."
php -r 'exit(PHP_VERSION_ID >= 80300 ? 0 : 1);' \
    || fail "PHP 8.3 or newer is required (found $(php -r 'echo PHP_VERSION;'))."

if [ ! -f .env ]; then
    cp .env.example .env
    step "Created .env from .env.example"
fi

# The browser reaches the app through the port-forwarded HTTPS URL, so links and
# assets must be generated against that URL rather than http://localhost.
if [ -n "${CODESPACE_NAME:-}" ]; then
    codespace_url="${CODESPACE_VSCODE_FORWARD_PORT_8000:-https://${CODESPACE_NAME}-8000.app.github.dev}"
    codespace_url="${codespace_url%/}"
    sed -i "s|^APP_URL=.*|APP_URL=${codespace_url}|" .env
    step "Set APP_URL to the forwarded codespace URL"
fi

mkdir -p database bootstrap/cache
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/framework/testing
mkdir -p storage/logs storage/app/public
[ -f database/database.sqlite ] || : > database/database.sqlite

install_composer_dependencies() {
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
}

step "Installing PHP dependencies"
if ! install_composer_dependencies; then
    if [ -z "${COMPOSER_AUTH:-}" ] && [ -n "${GITHUB_TOKEN:-}" ]; then
        # A codespace shares one outbound IP, so anonymous Composer downloads from
        # GitHub can hit the rate limit. Retry with the token the codespace already
        # carries, without printing it or writing it to a committed file.
        step "Retrying the download with the codespace token"
        export COMPOSER_AUTH
        COMPOSER_AUTH=$(printf '{"github-oauth":{"github.com":"%s"}}' "${GITHUB_TOKEN}")
        install_composer_dependencies || fail "composer install failed."
    else
        fail "composer install failed."
    fi
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    step "Generating the application key"
    php artisan key:generate --force
fi

step "Running migrations"
php artisan migrate --force

# Seed only when there is nobody to sign in as, so a rebuild does not duplicate
# the demo dataset.
user_count=$(php artisan tinker --execute='echo "USERCOUNT=".\App\Models\User::count();' 2> /dev/null | grep -oE 'USERCOUNT=[0-9]+' | tail -1 || true)
if [ "${user_count}" = "USERCOUNT=0" ] || [ -z "${user_count}" ]; then
    step "Seeding the development dataset"
    php artisan db:seed --force
fi

php artisan storage:link --quiet 2> /dev/null || true

step "Installing Node dependencies"
if [ -f package-lock.json ]; then
    npm ci --no-audit --no-fund --loglevel=error
else
    npm install --no-audit --no-fund
fi

step "Building frontend assets"
npm run build

php artisan optimize:clear --quiet

step "Setup complete."
step "Start the site:  bash .devcontainer/serve.sh"
step "Sign in with the DEFAULT_ADMIN_EMAIL / DEFAULT_ADMIN_PASSWORD pair in .env"
