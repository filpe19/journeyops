# Local development

## Requirements

- PHP 8.4+ with `pdo_sqlite`, `mbstring`, `openssl`, `dom` (PHP 8.5 used during development)
- Composer 2
- Node.js 20+ and npm (for the Vite/Tailwind build)

No Docker, database server, Redis or external API is required.

## First run

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

`./scripts/setup.sh` does everything except the last line.

`APP_URL` must match the address you serve on (default `http://127.0.0.1:8000`); it is also
used as the base URL for in-process synthetic traffic.

`APP_DEBUG=false` by default so error pages never show stack traces. Set it to `true` in your
own `.env` while developing if you want Laravel's debug pages; details are always written to
`storage/logs/laravel.log`.

## Frontend

```bash
npm run dev     # Vite dev server with hot reload
npm run build   # production assets in public/build
```

## Resetting the lab

```bash
php artisan demo:reset --force
```

This drops all tables, re-runs migrations, seeds the catalog (accounts, events) and replays about
three days of synthetic visitor traffic through the real web flows. The journey log
(`storage/logs/journey.jsonl`) is truncated first. Traffic timestamps are relative to the moment
you run it, so the same sequence of journeys is produced every time.

## Generating more traffic

```bash
php artisan demo:simulate --list
php artisan demo:simulate --all
php artisan demo:simulate share-link-browse-only returning-buyer-share-link
php artisan demo:simulate --all --url=http://127.0.0.1:8000   # via a running server
php artisan journeys:close-idle --minutes=0                    # close everything immediately
```

## Replaying the shared-link checkout journey

```bash
php artisan demo:replay-checkout                  # or ./scripts/replay_checkout_journey.sh
php artisan demo:replay-checkout --url=http://127.0.0.1:8000
```

It prints the journey code, which purchase stages were observed, the pages visited, the final
state of the visitor's account and the journey outcome. The exit code is `0` when the journey
completes with a payment and `1` otherwise.

## Tests and style

```bash
php artisan test
vendor/bin/pint
```

## Troubleshooting

| Symptom | Fix |
|---|---|
| `Vite manifest not found` | `npm run build` |
| `database/database.sqlite does not exist` | `touch database/database.sqlite && php artisan migrate --seed` |
| Port 8000 busy | `php artisan serve --port=8010` and update `APP_URL` |
| 419 Page Expired | the form was open longer than the session lifetime — reload the page |
