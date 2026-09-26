#!/usr/bin/env bash
# One-shot local setup: dependencies, environment, database, demo data and assets.
set -euo pipefail
cd "$(dirname "$0")/.."

composer install --no-interaction
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force
[ -f database/database.sqlite ] || touch database/database.sqlite
php artisan migrate:fresh --seed --force
npm install --no-audit --no-fund
npm run build

echo
echo "Ready. Start the app with: php artisan serve --host=127.0.0.1 --port=8000"
