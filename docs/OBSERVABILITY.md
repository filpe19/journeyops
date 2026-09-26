# Observability

The application records *journeys*: logical browsing sessions made of ordered *journey events*.
The data is written to two places at the same time:

1. **SQLite** — tables `journey_sessions` and `journey_events`.
2. **JSON Lines** — `storage/logs/journey.jsonl` (path configurable with `JOURNEY_LOG_PATH`).

## Journeys

A journey starts on the first tracked page of a visit and is identified by a UUID stored in the
HTTP session (it survives sign-in, because Laravel keeps session data when regenerating the ID).
The short code `JRN-XXXXXXXX` is the first 8 hex characters of the UUID.

A new journey starts when:

- the visitor has no journey yet;
- the previous one has ended (e.g. after a successful payment);
- the previous one has been idle for more than `JOURNEY_IDLE_MINUTES` (default 30);
- a tracked link (`?ref=…`) is opened and it maps to a different source than the current journey.

### Source

| `?ref=` | source |
|---|---|
| `share` (the link producers copy from their dashboard) | `event_share` |
| `newsletter` | `email` |
| `social` | `social` |
| none, entry on `/` | `homepage` |
| none, entry on `/sell` | `producer_landing` |
| anything else | `direct` |

### Outcome (derived, not stored)

| outcome | rule |
|---|---|
| `completed` | journey contains `payment_completed` |
| `active` | not ended yet |
| `abandoned` | ended, reached `buy_clicked`/`checkout_started`, no payment |
| `no_checkout` | ended without entering the purchase funnel |

`journeys:close-idle` (scheduled every 5 minutes) ends idle journeys; abandoned checkouts receive a
final `journey_abandoned` event.

## Event catalogue

| event | emitted by | notable metadata |
|---|---|---|
| `landing_viewed` | `/`, `/sell` | `page` |
| `event_page_viewed` | `GET /events/{slug}` | `event_id`, `ref` |
| `buy_clicked` | `POST /events/{slug}/buy` | `quantity`, `target_route` |
| `checkout_started` | `GET /checkout/{slug}` | `quantity` |
| `auth_required` | `GET /checkout/{slug}` as guest | `target_route`, `expected_destination` |
| `signup_started` | `GET /register` | `context` (`checkout` or `standalone`) |
| `signup_completed` | `POST /register` | `user_id`, `target_route` |
| `login_completed` | `POST /login` | `target_route` |
| `checkout_resumed` | `GET /checkout/{slug}` after authentication | `quantity` |
| `producer_onboarding_viewed` | `GET /producer/onboarding` | `has_producer_profile` |
| `producer_onboarding_completed` | `POST /producer/onboarding` | `target_route` |
| `order_created` | checkout submit | `order_id`, `order_reference`, `quantity`, `total` |
| `payment_started` | checkout submit | `order_id`, `provider` |
| `payment_completed` | checkout submit | `order_id`, `order_reference`, `transaction_reference` |
| `journey_abandoned` | `journeys:close-idle` | `last_event`, `idle_minutes` |

Every event also carries: `journey_uuid`, `source`, `user_role` (role of the signed-in user at that
moment, or `guest`), `event_slug` (when the step concerns an event), `previous_route`
(previous tracked route in the same journey) and `method`.

`target_route` is the path the response redirected to. `expected_destination` is where the visitor
should return once the step is done.

### Never recorded

Passwords, CSRF tokens, cookies, session IDs, IP addresses, payment card data. Keys listed in
`config/journey.php` → `redacted_keys` are dropped defensively.

## JSONL format

```json
{"timestamp":"2026-09-26T10:15:02+00:00","journey_id":"<uuid>","journey_code":"JRN-1A2B3C4D",
 "source":"event_share","event":"buy_clicked","route":"/events/ai-builders-night-2026/buy",
 "user_id":null,"user_role":"guest","metadata":{…}}
```

Useful one-liners:

```bash
jq -c 'select(.source=="event_share") | [.timestamp,.journey_code,.event,.user_role]' storage/logs/journey.jsonl
jq -r 'select(.journey_code=="JRN-1A2B3C4D") | "\(.timestamp) \(.event) \(.route)"' storage/logs/journey.jsonl
grep '"event":"payment_completed"' storage/logs/journey.jsonl | wc -l
```

## SQL

```sql
-- journeys with their user and final role
SELECT js.uuid, js.source, js.started_at, u.email, u.role
FROM journey_sessions js LEFT JOIN users u ON u.id = js.user_id
ORDER BY js.started_at DESC;

-- event sequence of one journey
SELECT je.occurred_at, je.event_name, je.route, je.metadata
FROM journey_events je JOIN journey_sessions js ON js.id = je.journey_session_id
WHERE js.uuid = :uuid ORDER BY je.occurred_at, je.id;
```

## Tools

- `/ops` — admin dashboard (sales, journeys by source, outcomes, sequences, per-journey timeline).
- `php artisan ops:summary | ops:journeys | ops:journey` — the same data in the terminal (`--json` available).
- `X-Journey-Id` response header — correlate a browser session (DevTools) with its journey.
