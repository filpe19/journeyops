# Ticket Lab — Application Architecture

JourneyOps Ticket Lab is a single Laravel 13 application backed by SQLite. There are no queues,
caches or external services on the request path; everything needed to reproduce behaviour is in
this repository.

## Actors

| Role | How the account is created | Home after sign-in |
|---|---|---|
| buyer | public signup (`/register`) | `/account` |
| producer | public signup from the organizer landing page (`/sell` → `/register?type=producer`), then onboarding | `/producer` |
| admin | seeded only | `/ops` |

## Request flows

### Buying a ticket

```
GET  /events/{slug}[?ref=share]      EventController@show        event_page_viewed
POST /events/{slug}/buy              CheckoutController@buy      buy_clicked      (stores PurchaseIntent in session)
GET  /checkout/{slug}                CheckoutController@show
     ├─ guest ─► checkout_started, auth_required ─► redirect()->guest('/login')   (intended URL = checkout)
     └─ signed in ─► checkout_started  (or checkout_resumed when returning from authentication)
POST /checkout/{slug}                CheckoutController@store    order_created, payment_started, payment_completed
GET  /orders/{id}                    OrderController@show        confirmation
```

`App\Support\PurchaseIntent` keeps the event and quantity in the session while the visitor moves
between checkout and the authentication pages; the sign-in and signup pages use it to show which
ticket is being bought.

`App\Actions\Orders\PlaceOrder` creates the order (pending), charges it through
`App\Payments\SimulatedPaymentGateway` (always approves, deterministic reference `SIM-000123`)
and marks it paid.

### Authentication

```
GET  /login     LoginController@create
POST /login     LoginController@store       login_completed
GET  /register  RegisterController@create   signup_started
POST /register  RegisterController@store    signup_completed
POST /logout
```

Account creation is implemented by `App\Actions\Accounts\RegisterAccount`. Where the user goes after
signing in or signing up is decided by `App\Support\PostAuthenticationRedirect`.

### Producer area

```
GET/POST /producer/onboarding           producer_onboarding_viewed / producer_onboarding_completed
GET      /producer                      dashboard (requires onboarding)
GET/POST /producer/events[/create]      create draft events
POST     /producer/events/{slug}/publish
```

Protected by `auth`, `role:producer` and, except for onboarding, `producer.onboarded`.

### Operations

`/ops` and `/ops/journeys/{uuid}` require the `view-ops` gate (admin role). The same read model
(`App\Journey\OpsReport`) powers the `ops:*` Artisan commands.

`GET /health` returns `{"status":"ok","application":…,"database":"ok","timestamp":…}`.

## Data model

```
users ─┬─< events (producer_id) ─< orders >─ users
       ├── producer_profiles (1:1)
       └─< journey_sessions ─< journey_events
```

| Table | Notes |
|---|---|
| `users` | `role` ∈ buyer, producer, admin |
| `producer_profiles` | organizer display name; created during onboarding |
| `events` | `slug` is the public route key; `price` in cents; `status` ∈ draft, published, cancelled |
| `orders` | `reference`, `quantity`, `total` (cents), `status` ∈ pending, paid, cancelled, `paid_at` |
| `journey_sessions` | `uuid`, `anonymous_actor_id`, `source`, `entry_route`, `event_id`, `user_id`, `started_at`, `last_activity_at`, `ended_at` |
| `journey_events` | `event_name`, `route`, `user_id`, `metadata` (JSON), `occurred_at` |

Sessions are stored in the `sessions` table (database driver).

## Synthetic traffic

`App\Demo\SyntheticBrowser` is a tiny headless browser: it keeps cookies, follows redirects, clicks
links and submits forms with the values a real browser would send (hidden inputs, checked radios,
selected options). Two transports exist:

- `KernelTransport` boots a fresh copy of the application for each request and dispatches it
  through the HTTP kernel — the full middleware stack (sessions, CSRF, auth) runs, no socket needed.
  It supports a simulated clock, which the seeder uses to spread traffic over several days.
- `HttpTransport` sends real HTTP requests to a running server (`--url=`).

`App\Demo\TrafficScenarios` defines the synthetic visitors. `DemoTrafficSeeder` replays a fixed
plan of scenarios at fixed offsets from "now", then closes idle journeys.

## Scheduling

`routes/console.php` schedules `journeys:close-idle` every five minutes
(`php artisan schedule:work` locally).
