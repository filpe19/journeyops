# AGENTS.md — JourneyOps Ticket Lab

Orientation for engineers and coding agents working in this repository.

## What this system is

A small digital ticketing platform (Laravel 13, SQLite) with first-class user-journey telemetry.
Producers publish events and share a public link; buyers open the link, check out, authenticate
if needed and pay through a simulated, always-approving gateway. Every step is recorded as a
journey event so behaviour can be reconstructed after the fact.

All data is synthetic (`@example.test`). Never import real data. See `docs/DATA_POLICY.md`.

## Architecture at a glance

```
Browser ──► routes/web.php ──► Controllers ──► Actions / Support services ──► Eloquent models ──► SQLite
                                   │
                                   └──► App\Journey\JourneyTracker ──► JourneyRecorder ──► journey_events (DB)
                                                                                     └──► storage/logs/journey.jsonl
```

| Area | Key files |
|---|---|
| Public catalog | `app/Http/Controllers/EventController.php`, `resources/views/events/*` |
| Purchase funnel | `app/Http/Controllers/CheckoutController.php`, `app/Actions/Orders/PlaceOrder.php`, `app/Support/PurchaseIntent.php`, `app/Payments/SimulatedPaymentGateway.php` |
| Authentication & signup | `app/Http/Controllers/Auth/*`, `app/Actions/Accounts/RegisterAccount.php`, `app/Support/PostAuthenticationRedirect.php` |
| Producer area | `app/Http/Controllers/Producer/*`, `app/Http/Middleware/EnsureProducerOnboarded.php` |
| Authorization | `app/Http/Middleware/EnsureUserHasRole.php` (`role:` alias), gate `view-ops` in `AppServiceProvider` |
| Telemetry | `app/Journey/*` (`JourneyTracker`, `JourneyRecorder`, `JourneyLog`, `IdleJourneyCloser`, `OpsReport`) |
| Operations | `app/Http/Controllers/Ops/*`, `resources/views/ops/*`, `app/Console/Commands/Ops*.php` |
| Synthetic traffic | `app/Demo/*` (headless `SyntheticBrowser`, `TrafficScenarios`), `database/seeders/*` |
| Configuration | `config/accounts.php`, `config/journey.php` |

Domain model: `User` (role: buyer | producer | admin), `ProducerProfile`, `Event`, `Order`
(pending | paid | cancelled), `JourneySession`, `JourneyEvent`. Money is stored in cents.

More detail: `docs/ARCHITECTURE.md`.

## Commands

```bash
# setup
composer install && cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate:fresh --seed
npm install && npm run build

# run
php artisan serve --host=127.0.0.1 --port=8000

# test
php artisan test                       # full suite (PHPUnit, in-memory SQLite)
php artisan test --filter=CheckoutTest # a single class

# operate / investigate
php artisan ops:summary --days=7
php artisan ops:journeys --with-sequence [--source=event_share]
php artisan ops:journey JRN-XXXXXXXX   # or the full UUID; add --json for machine output
php artisan journeys:close-idle

# demo data
php artisan demo:reset --force
php artisan demo:simulate --list | --all | <scenario...> [--url=http://127.0.0.1:8000]
php artisan demo:replay-checkout [--url=...]
```

## Observability available

- **Database**: `journey_sessions` (one row per logical visit: source, entry route, event, user,
  start/last activity/end) and `journey_events` (event name, route, user, JSON metadata, time).
  `sqlite3 database/database.sqlite` works for ad-hoc SQL.
- **JSON Lines**: `storage/logs/journey.jsonl`, one object per event — use `grep` / `jq`.
- **Application log**: `storage/logs/laravel.log`.
- **/ops**: admin-only page with sales, journeys by source, outcomes and event sequences.
- **Response header** `X-Journey-Id` links any HTTP response to its journey.

Event catalogue, metadata fields and outcome rules: `docs/OBSERVABILITY.md`.

## Conventions

- PSR-12 / Laravel Pint style (`vendor/bin/pint`). Controllers stay thin; multi-step domain
  operations live in `app/Actions`, cross-cutting helpers in `app/Support`.
- Enums in `app/Enums` for roles and statuses; never compare raw strings in new code.
- Every user-visible step of the purchase or signup funnels should emit a journey event via
  `JourneyTracker::record()`. Never put credentials, tokens or personal data in metadata
  (`config/journey.php` → `redacted_keys` is a last line of defence, not the primary one).
- Feature tests use `RefreshDatabase` with in-memory SQLite and write the journey log to
  `storage/framework/testing/journey.jsonl`.
- Keep the app dependency-free: no external APIs, no real payment providers.

## Safety rules for agents

- Work only inside this repository. Do not add remotes, push, or deploy.
- Use only synthetic data (`@example.test`). Do not introduce secrets; `.env` is git-ignored.
- `demo:reset` wipes the local database and the journey log — that is expected and safe here.
