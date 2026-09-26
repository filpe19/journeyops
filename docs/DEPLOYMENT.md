# Public Demo Deployment

The public demo runs at **https://journeyops.tickiton.com.br**. It serves the current `main`, which is the state IBM Bob verified (`bob-final-verified`) plus the presentation layer added afterwards. The broken baseline is never deployed.

## Topology

```
Internet ──443──► Nginx (host, vhost "journeyops") ──static──► /var/www/journeyops/public
                         │
                         └── /index.php ──FastCGI 127.0.0.1:9085──► journeyops-app container (php:8.4-fpm)
                                                                     └── SQLite: /var/www/journeyops/database/database.sqlite
```

- **Isolation.** The VPS hosts other applications. JourneyOps has its own Nginx virtual host, its own directory and its own PHP-FPM container ([`deploy/compose.yaml`](../deploy/compose.yaml)). The host's PHP packages and the other sites' configuration are not touched. The container's FastCGI port is bound to `127.0.0.1` only.
- **Why a container.** The host's PHP has no SQLite driver, and adding one would have upgraded PHP for every application on the server. The official `php:8.4-fpm` image already includes `pdo_sqlite`.
- **Config files:** [`deploy/`](../deploy/): `Dockerfile`, `php.ini`, `fpm-pool.conf`, `compose.yaml`, `nginx-journeyops.conf`, `cron.journeyops`, `deploy-production.sh`.
- **Environment.** A server-only `.env` (`640 deploy:www-data`, never committed): `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://journeyops.tickiton.com.br`, SQLite, secure session cookies. Synthetic data and the simulated payment gateway only.
- **TLS.** Let's Encrypt via webroot. Renewal is handled by `certbot.timer` with a deploy hook that reloads Nginx.

## Public-demo hardening

| Measure | Where |
|---|---|
| `/ops` and `/ops/*` return 404 publicly. The ops console shows every visitor's journeys, so it isn't part of the public demo. Visitors see their own journey on the order confirmation page. | Nginx vhost |
| No operator credentials anywhere in the public UI | Blade views |
| Registration asks for a test identity (`name@example.test`) and no personal data | `auth/register.blade.php` |
| `X-Robots-Tag: noindex, nofollow` and a host-only `robots.txt` that disallows everything | Nginx vhost |
| HSTS, `nosniff`, `X-Frame-Options: DENY`, Referrer-Policy, Permissions-Policy, a CSP compatible with the Vite build | Nginx vhost |
| Dotfiles, `.env`, SQLite, logs, lockfiles and any PHP file other than `index.php` are denied | Nginx vhost |
| `display_errors=Off`, `expose_php=Off`, `APP_DEBUG=false`: no stack traces | `deploy/php.ini`, `.env` |

## Scheduler and demo data lifecycle

`/etc/cron.d/journeyops` (server time zone: `America/Belem`, UTC−3):

| Job | When | Command |
|---|---|---|
| Laravel scheduler (`journeys:close-idle` every 5 min) | every minute | `docker exec … php artisan schedule:run` |
| Reset synthetic demo data | **daily at 04:30 America/Belem (07:30 UTC)** | `docker exec … php artisan demo:reset --force` |

Both jobs share a `flock` lock, so the scheduler skips a tick while a reset is running. There is **no HTTP reset endpoint**, and visitors cannot run Artisan commands. The reset only touches the JourneyOps SQLite file and journey log, and takes a few seconds. A visitor who is mid-checkout at 04:30 BRT would have to sign up again.

## Deploying a new version

```bash
ssh deploy@<vps> /var/www/journeyops/deploy/deploy-production.sh
```

The script fetches `origin/main` and **fast-forwards only**. It then runs `composer install --no-dev`, `npm ci && npm run build`, rebuilds or starts the container, then `migrate --force` and `optimize`, and finally a health check. It never resets demo data.

## Rollback

Every release is a commit on `main`, and public releases are tagged (`public-demo-v1`, …). To roll back without rewriting history:

```bash
cd /var/www/journeyops
git checkout --detach <previous-good-commit-or-tag>
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
npm ci && npm run build
sudo docker exec -u www-data journeyops-app php artisan package:discover
sudo docker exec -u www-data journeyops-app php artisan optimize
```

Return to normal with `git checkout main` and then the deploy script. The experiment tags `baseline-pre-bob` and `bob-final-verified` are **not** deployment targets.

## Checks

```bash
curl -s https://journeyops.tickiton.com.br/health        # {"status":"ok",…,"database":"ok"}
curl -s -o /dev/null -w "%{http_code}" https://journeyops.tickiton.com.br/ops   # 404
sudo docker exec -u www-data journeyops-app php artisan about --only=environment
```
