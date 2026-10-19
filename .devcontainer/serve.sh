#!/usr/bin/env bash
# Start the site on the port GitHub Codespaces forwards (8000 by default).
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

port="${PORT:-8000}"

[ -f .env ] || { echo "No .env yet. Run: bash .devcontainer/bootstrap.sh"; exit 1; }
[ -d vendor ] || { echo "PHP dependencies missing. Run: bash .devcontainer/bootstrap.sh"; exit 1; }

if [ ! -f public/build/manifest.json ]; then
    echo "Built assets missing. Running npm run build first."
    npm run build
fi

[ -f database/database.sqlite ] || : > database/database.sqlite

echo "Beloved Schools is listening on port ${port}. In Codespaces use the forwarded URL in the Ports tab."
exec php artisan serve --host=0.0.0.0 --port="${port}"
