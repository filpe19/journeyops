# Demo Guide

**Live public demo:** https://journeyops.tickiton.com.br. Start at [`/demo`](https://journeyops.tickiton.com.br/demo) for the guided walkthrough. The steps below run the same thing on your own machine, and also let you reproduce the broken baseline.

Reproduce the JourneyOps demonstration on your own machine: the corrected journey on `main`, and optionally the original failure at `baseline-pre-bob`, without touching your main checkout.

Everything runs locally with synthetic data. No accounts, API keys or network services are needed.

## 1. Setup

Requirements: PHP 8.4+ (with `pdo_sqlite`, `mbstring`, `openssl`, `dom`), Composer 2, Node.js 20+ and npm.

```bash
git clone https://github.com/filpe19/journeyops.git
cd journeyops
./scripts/setup.sh
```

`setup.sh` runs `composer install`, creates `.env` from `.env.example`, generates the app key, creates `database/database.sqlite`, runs `migrate:fresh --seed`, and builds the frontend assets. To start the web UI:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

| URL | What |
|---|---|
| http://127.0.0.1:8000/events/ai-builders-night-2026?ref=share | The shared event link used in the demo |
| http://127.0.0.1:8000/ops | Operations view (sign in as `admin@example.test` / `password`) |
| http://127.0.0.1:8000/health | Health check |

Demo accounts (local synthetic database only, password `password` for all):

| Email | Role |
|---|---|
| `admin@example.test` | admin (access to `/ops`) |
| `producer@example.test` | producer (*NovaStage Events*) |
| `buyer.one@example.test`, `buyer.two@example.test`, `buyer.four@example.test`, `buyer.five@example.test` | buyer |

The CLI steps below do **not** need the server running.

## 2. Reset the lab

```bash
php artisan demo:reset --force
```

This wipes the local SQLite database and `storage/logs/journey.jsonl`, reseeds accounts and events, and replays about three days of synthetic traffic through the real web flows. The same journeys are produced on every run, with timestamps relative to now. This is a local lab database, so the reset is safe.

## 3. Replay the checkout journey (current, fixed state)

```bash
php artisan demo:replay-checkout
echo "exit code: $?"
```

A brand-new visitor opens the shared link, clicks **Buy ticket**, is asked to sign in at checkout, creates an account and pays. Expected output (the journey code and order number vary):

```
Journey: JRN-XXXXXXXX …
Entry: /events/ai-builders-night-2026?ref=share (source: event_share)
Visitor: new.visitor@example.test

event_page_viewed .. OBSERVED
buy_clicked .. OBSERVED
checkout_started .. OBSERVED
auth_required .. OBSERVED
signup_started .. OBSERVED
signup_completed .. OBSERVED
checkout_resumed .. OBSERVED
order_created .. OBSERVED
payment_started .. OBSERVED
payment_completed .. OBSERVED

Other events: none
Browser path: /events/ai-builders-night-2026 -> /events/ai-builders-night-2026/buy -> /checkout/ai-builders-night-2026 -> /login -> /register -> /register -> /checkout/ai-builders-night-2026 -> /checkout/ai-builders-night-2026 -> /orders/9
Final page: /orders/9 (HTTP 200)
User role: buyer producer profile: no
Orders: 1

Outcome: COMPLETED
exit code: 0
```

To send the replay over real HTTP to a running server instead of in-process:

```bash
php artisan demo:replay-checkout --url=http://127.0.0.1:8000
# or: ./scripts/replay_checkout_journey.sh --url=http://127.0.0.1:8000
```

## 4. Reproduce the baseline failure (optional)

The tag `baseline-pre-bob` is the lab before IBM Bob changed anything. Inspect it without switching or resetting your current branch.

**Read-only inspection:**

```bash
git show baseline-pre-bob:config/accounts.php | grep default_type
#   'default_type' => env('ACCOUNT_DEFAULT_TYPE', 'producer'),

git diff baseline-pre-bob bob-final-verified -- app config .env.example   # the fix
git diff --stat baseline-pre-bob bob-final-verified -- tests              # the regression tests
```

**Run it in a separate worktree.** This leaves your checkout, its `.env` and its database untouched:

```bash
git worktree add ../journeyops-baseline baseline-pre-bob
cd ../journeyops-baseline
composer install          # run a real install; do not symlink vendor/ from the main checkout
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
npm install && npm run build

php artisan test                    # 45 passed (165 assertions): the suite is green
php artisan demo:reset --force
php artisan demo:replay-checkout; echo "exit code: $?"
```

Expected baseline replay:

```
signup_completed .. OBSERVED
checkout_resumed .. MISSING
order_created .. MISSING
payment_started .. MISSING
payment_completed .. MISSING

Other events: producer_onboarding_viewed, journey_abandoned
Browser path: … -> /login -> /register -> /register -> /producer/onboarding
Final page: /producer/onboarding (HTTP 200)
User role: producer producer profile: no
Orders: 0

Outcome: ABANDONED
exit code: 1
```

Clean up when done:

```bash
cd -
git worktree remove ../journeyops-baseline
```

> **Why not symlink `vendor/`?** The project uses Composer's optimized autoloader. A symlinked `vendor/` resolves `App\` classes to the *original* checkout's `app/` directory, so the baseline worktree would silently run the fixed controller code.

> **Why `cp .env.example`?** The baseline `.env.example` does not set `ACCOUNT_DEFAULT_TYPE`, so the baseline config default (`producer`) applies. An `.env` copied from the fixed checkout sets `ACCOUNT_DEFAULT_TYPE=buyer` and would hide the defect.

## 5. Inspect the telemetry

```bash
php artisan ops:summary --days=7
php artisan ops:journeys --with-sequence --source=event_share
php artisan ops:journey JRN-XXXXXXXX              # timeline of one journey; add --json for machine output
```

Straight from the JSON Lines log:

```bash
# every signup: which role was created and where the user was sent
jq -c 'select(.event=="signup_completed") | [.journey_code, .user_role, .metadata.target_route]' storage/logs/journey.jsonl

# the full event sequence of one journey
jq -r 'select(.journey_code=="JRN-XXXXXXXX") | "\(.timestamp) \(.event) \(.user_role) \(.route)"' storage/logs/journey.jsonl
```

What to look for:

| Field | Baseline | Fixed |
|---|---|---|
| `signup_completed.user_role` (checkout signup) | `producer` | `buyer` |
| `signup_completed.metadata.target_route` | `/producer/onboarding` | `/checkout/ai-builders-night-2026` |
| Next event | `producer_onboarding_viewed` | `checkout_resumed` |
| Outcome in `ops:journeys` | `abandoned` | `completed` |

The legitimate producer signup, `source=producer_landing`, looks the same in both states: `signup_completed (user_role=producer, target_route=/producer/onboarding)` → `producer_onboarding_viewed` → `producer_onboarding_completed`.

```bash
php artisan ops:journeys --with-sequence --source=producer_landing
```

## 6. Run the tests

```bash
php artisan test                                   # 51 passed (206 assertions) on main: the 48 Bob-verified tests + 3 demo-page tests
php artisan test --filter="CheckoutTest|RegistrationTest"
php artisan test --filter=ProducerAreaTest
```

The three regression tests added by IBM Bob:

- `CheckoutTest::test_guest_who_registers_during_checkout_returns_to_checkout_as_buyer`
- `CheckoutTest::test_checkout_registration_forces_buyer_even_when_default_is_producer`
- `RegistrationTest::test_registration_without_account_type_defaults_to_buyer`

## 7. Walk the demo in the browser

This is the exact sequence the public demo is designed for. It needs no explanation from the presenter.

1. `php artisan serve` and open http://127.0.0.1:8000/ in a private window: the JourneyOps overview.
2. Optional: toggle **Before IBM Bob / After IBM Bob** in the hero trace, or scroll to **Before → after**.
3. Click **Launch live demo**. It opens `/events/ai-builders-night-2026?ref=share`, marked *JourneyOps demo scenario*.
4. Click **Buy ticket**. Checkout asks you to sign in and shows the ticket you're buying.
5. Choose **Create an account**. The page reads *Create your account to continue checkout*, and no account-type selector is shown. Register with any new `@example.test` address and press **Create account and continue**.
6. You land back on checkout as a buyer (*Review and pay*, marked *Simulated checkout*). Press **Pay $49.00**.
7. The confirmation page says **Purchase complete**. Next to it, *What the telemetry recorded* reads your journey from the telemetry store: `COMPLETED`, with `checkout_resumed`, `order_created` and `payment_completed` recorded and `signup_completed · user_role=buyer`.
8. **View journey evidence** leads to step 4 of `/demo`. Locally, you can also open the full ops console: sign out, sign in as `admin@example.test` / `password`, open `/ops` and select your journey to see its timeline, with metadata per step.

On the **public** demo (https://journeyops.tickiton.com.br), `/ops` is blocked because it lists every visitor's journeys. Each visitor sees their own journey on the order confirmation page instead.

`/demo` walks through the same five steps with buttons for each one.

### Public demo reset

The public UI deliberately has **no reset endpoint**. On the public instance, a cron job resets the synthetic data daily at 04:30 America/Belem (07:30 UTC). See [DEPLOYMENT.md](DEPLOYMENT.md). Locally, resetting is an operator action:

```bash
php artisan demo:reset --force
```

Judges' registrations and orders accumulate until an operator runs it. Everything is synthetic.

## 8. Where the IBM Bob evidence is

| Step | File |
|---|---|
| Task 01: investigation | [`bob_sessions/journeyops_task01_operational_analysis.md`](../bob_sessions/journeyops_task01_operational_analysis.md) |
| Task 02: remediation plan | [`bob_sessions/journeyops_task02_remediation_plan.md`](../bob_sessions/journeyops_task02_remediation_plan.md) |
| Task 03: implementation | [`bob_sessions/journeyops_task03_implementation.md`](../bob_sessions/journeyops_task03_implementation.md) |
| Task 04: independent verification | [`bob_sessions/journeyops_task04_final_verification.md`](../bob_sessions/journeyops_task04_final_verification.md) |
