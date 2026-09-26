# JourneyOps Ticket Lab

A production-like digital ticketing laboratory with user-journey telemetry for AI-assisted software investigation.

JourneyOps Ticket Lab is a small but complete ticketing platform that runs entirely on a laptop:

- **Organizers (producers)** create events, publish them and share a public link.
- **Buyers** open the link, buy tickets, sign in or create an account at checkout, and pay (simulated, deterministic).
- **Every step of every visit** is recorded as a *journey* in SQLite and mirrored to a JSON Lines log.
- An **operations view** (`/ops`) and a set of **`ops:*` Artisan commands** let an engineer check sales and reconstruct any journey.
- A **synthetic traffic generator** drives the real web flows (routes, middleware, controllers, sessions, CSRF) to produce realistic, reproducible history.

All data is synthetic. See [docs/DATA_POLICY.md](docs/DATA_POLICY.md).

## Stack

| Layer | Choice |
|---|---|
| Language / framework | PHP 8.4+ (tested on 8.5), Laravel 13 |
| Database | SQLite (`database/database.sqlite`) |
| Frontend | Blade + Tailwind CSS 4, built with Vite |
| Tests | PHPUnit 12 (`php artisan test`) |
| External services | none (no payment gateway, no third-party APIs, no Docker) |

## Quick start

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

Or run `./scripts/setup.sh` which performs all of the above except starting the server.

## URLs

| What | URL |
|---|---|
| Home / catalog | http://127.0.0.1:8000/ |
| Demo event (shared link) | http://127.0.0.1:8000/events/ai-builders-night-2026?ref=share |
| Operations view | http://127.0.0.1:8000/ops (admin only) |
| Health check | http://127.0.0.1:8000/health |
| Organizer landing | http://127.0.0.1:8000/sell |

## Demo accounts

All passwords are `password`. These accounts exist only in the local synthetic database.

| Email | Role |
|---|---|
| `admin@example.test` | admin (access to `/ops`) |
| `producer@example.test` | producer — *NovaStage Events* |
| `buyer.one@example.test`, `buyer.two@example.test`, `buyer.four@example.test`, `buyer.five@example.test` | buyer |

`buyer.three@example.test` and `producer.lumen@example.test` are created by the traffic generator through the public signup flow.

## Tests

```bash
php artisan test
```

## Operational commands

```bash
php artisan ops:summary [--days=1] [--json]          # sales + journey health for a window
php artisan ops:journeys [--limit=25] [--source=event_share] [--with-sequence] [--json]
php artisan ops:journey JRN-XXXXXXXX [--json]        # full timeline of one journey (code or UUID)
php artisan journeys:close-idle [--minutes=30]       # end idle journeys (scheduled every 5 min)
```

## Demo data and traffic

```bash
php artisan demo:reset --force        # wipe DB + journey log, reseed accounts, events and ~3 days of traffic
php artisan demo:seed                 # same data into an empty, migrated database
php artisan demo:simulate --list      # available synthetic visitor scenarios
php artisan demo:simulate --all       # run every scenario now
php artisan demo:simulate returning-buyer-share-link newsletter-buyer
php artisan demo:replay-checkout      # new visitor from the shared link goes through checkout with signup
./scripts/replay_checkout_journey.sh  # same as above
```

Traffic runs in-process by default (no server needed). Add `--url=http://127.0.0.1:8000` to `demo:simulate` or `demo:replay-checkout` to send it over HTTP to a running server instead.

`./scripts/smoke.sh [base-url]` checks the main pages of a running instance.

## Documentation

- [AGENTS.md](AGENTS.md) — orientation for engineers and coding agents
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- [docs/OBSERVABILITY.md](docs/OBSERVABILITY.md)
- [docs/LOCAL_DEVELOPMENT.md](docs/LOCAL_DEVELOPMENT.md)
- [docs/DATA_POLICY.md](docs/DATA_POLICY.md)

## Repository layout

```
app/            application code (see docs/ARCHITECTURE.md)
bob_sessions/   IBM Bob task-session screenshots for the hackathon submission
database/       migrations, factories, seeders (synthetic data only)
docs/           architecture, observability, local development, data policy
resources/      Blade views, CSS, JS
routes/         web routes and scheduled commands
scripts/        setup, smoke test and journey replay helpers
tests/          PHPUnit feature and unit tests
```
