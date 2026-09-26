# Data policy

- **All data in this repository and in the generated database is synthetic.** Names such as
  "Buyer One", "Riley Chen", "NovaStage Events" or "Lumen Collective" are invented.
- Every email address uses the reserved `example.test` domain and cannot receive mail.
- **No external data source was needed.** Accounts, events, orders and journeys are created by
  seeders and by the synthetic traffic generator in `app/Demo`, which drives the application's own
  web flows.
- **No customer information was used** — no production databases, dumps, logs, analytics exports
  or screenshots from any real system.
- No personal data is collected by the telemetry: no IP addresses, phone numbers, national IDs,
  passwords, tokens or cookies are stored in `journey_events` or `storage/logs/journey.jsonl`.
- Payments are simulated locally; no card data is requested or stored.
- Demo accounts use the password `password` and exist only in the local SQLite database.

This approach satisfies the privacy requirements of the IBM Bob 2.0 Hackathon: the
repository, its database and its logs can be shared publicly without exposing anyone's information.
