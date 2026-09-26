#!/usr/bin/env bash
# Deploys the latest origin/main of the public JourneyOps demo on the VPS.
#   /var/www/journeyops/deploy/deploy-production.sh
# Fast-forward only; never resets demo data (that is the daily cron job).
set -euo pipefail

APP=/var/www/journeyops
COMPOSE="sudo docker compose -f $APP/deploy/compose.yaml"
ARTISAN="sudo docker exec -u www-data journeyops-app php artisan"

cd "$APP"
before=$(git rev-parse --short HEAD)

git fetch --quiet origin main
git merge --ff-only origin/main

composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts --quiet
npm ci --no-audit --no-fund --loglevel=error
npm run build --silent

$COMPOSE up -d --build --quiet-pull
$ARTISAN package:discover --ansi >/dev/null
$ARTISAN migrate --force
$ARTISAN optimize

after=$(git rev-parse --short HEAD)
echo "Deployed $before -> $after"
curl -fsS --max-time 10 --resolve journeyops.tickiton.com.br:443:127.0.0.1 https://journeyops.tickiton.com.br/health >/dev/null && echo "Health OK"
