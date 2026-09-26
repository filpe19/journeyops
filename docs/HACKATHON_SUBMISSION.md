# Hackathon Submission — Draft Copy

Draft text for the IBM Bob 2.0 Hackathon submission form. Each section can be pasted independently. Placeholders marked **[TBD …]** are not yet available.

---

## Project Name

JourneyOps

## Short Tagline

From broken user journeys to verified fixes with IBM Bob.

## One-line Description

JourneyOps gives IBM Bob evidence of what actually happened to users. Bob can then find, fix, test, replay and independently verify defects that passing test suites miss.

## Short Description

The test suite was green. The user journey wasn't. JourneyOps uses a controlled synthetic application to show how IBM Bob can investigate, fix and verify a production-style user-journey failure from operational evidence. It is a workflow prototype that feeds IBM Bob recorded user-journey telemetry, application state and source code instead of a hand-written bug ticket. In a synthetic ticketing lab, Bob was only asked whether purchasing looked healthy. It independently found that visitors who signed up at checkout were being turned into event organizers and losing their purchase. It then traced the cause to the code, planned and implemented a two-layer fix, added regression tests and replayed the journey. A separate Bob task verified the result: ABANDONED became COMPLETED, and the suite went from 45 to 48 tests with zero regressions.

## Long Description

Most coding agents start when a developer already knows what needs to be fixed. JourneyOps starts one step earlier: understanding what actually happened to the user.

Before the IBM Bob investigation began, we prepared a controlled laboratory: a small but realistic ticketing application (the Ticket Lab: Laravel 13, SQLite). The ticketing app is the test bed, not the product. In it, every step of every visit is recorded as a journey event. Each event carries the user's role, the route, where the response redirected and where the user was expected to go next. A synthetic traffic generator drives the real HTTP flows, so the lab produces a deterministic, production-like history of visits, orders and abandonments.

We deliberately seeded the lab with a realistic defect of the kind that slips through CI, and left no hints about it in the repository. The tag `baseline-pre-bob` preserves this exact pre-Bob state. When a new visitor opens a shared event link, clicks Buy, and creates an account at the checkout sign-in wall, the signup form hides the account-type selector. The missing value falls back to a config default of `producer`, and new producers are routed to organizer onboarding before the saved checkout URL is honoured. The visitor becomes an event organizer and the purchase is lost. All 45 tests pass.

Starting from that baseline, we ran IBM Bob as four separate tasks:

1. **Investigation (Ask mode).** A generic production-engineering prompt: "is the ticket purchasing experience operating normally?", with explicit instructions not to assume a bug. Bob used subagents to read the journey log, configuration and source in parallel. It separated normal abandonments from one anomalous journey and traced that journey to the exact lines of code responsible.
2. **Remediation planning (Plan mode, `create-plan` skill).** Bob compared four remediation options. It rejected the one that would weaken producer onboarding and defined regression tests, acceptance criteria and a replay plan.
3. **Implementation (Agent mode).** Bob re-validated its own plan and found that a config-only fix would still fail if a deployment sets `ACCOUNT_DEFAULT_TYPE=producer`. It implemented a two-layer fix instead: buyer role is enforced whenever a purchase is in progress, and the general default is corrected. It added three regression tests, ran the full suite (48/48), reset the lab and replayed the original journey: COMPLETED.
4. **Independent verification (Agent mode, no edits allowed).** A fresh task re-audited git state, source, tests, an HTTP replay against a running server, and telemetry. Result: VERIFIED. Producer signup still goes to onboarding, and no source files were modified by the verifier.

Every Bob task was exported and committed next to the code, together with session screenshots. Git tags mark the state before Bob (`baseline-pre-bob`) and the verified state (`bob-final-verified`), so anyone can reproduce the before and after locally with a single command.

## Problem

Many production failures are not crashes. They are wrong redirects, unexpected role assignments, state-dependent paths and silently leaking funnels. These pass unit and feature tests because each piece works on its own; only the full user journey fails. The hardest part of fixing them is not writing the patch. It is reconstructing what happened to the user and connecting that behaviour back to the code. Coding agents usually start after that step, from a ticket a human already wrote.

## Solution

JourneyOps makes user-journey evidence first-class context for an AI engineering agent:

production-like telemetry → anomaly investigation → source-code tracing → root cause → remediation plan → code fix → regression tests → journey replay → independent verification.

The application records structured journey events (role, route, redirect target, expected destination, outcome). Operational commands expose them as a read model. IBM Bob starts from that evidence, and every conclusion is checked three ways: tests, a deterministic replay of the original journey, and telemetry from the replay.

## How IBM Bob Is Used

- **Ask mode, investigation.** Bob read journey telemetry (`journey.jsonl`), logs, docs and code with no edits. It classified 16 journeys as normal or suspicious and identified the anomalous one with concrete field-level evidence.
- **Subagents.** Bob delegated parallel read-only exploration of telemetry, source and configuration (Tasks 01 and 02).
- **Plan mode + `create-plan` skill.** Bob confirmed the root cause, compared four options with trade-offs, and set out a regression test plan, acceptance criteria and a risk table.
- **Agent mode, implementation.** Bob re-validated the plan, edited code with `apply_diff`, added regression tests and ran `php artisan test`. It reset the lab and replayed the journey with the project's own `demo:replay-checkout` tool, then inspected the replayed telemetry.
- **Agent mode, independent verification.** A separate task, forbidden from editing code, re-ran tests, replayed the journey over HTTP, checked telemetry and git state, and produced an audit report.
- **Evidence.** All four tasks were exported to Markdown and screenshotted, including mode, context usage and Bobcoin cost, and committed to the repository.

## Technical Implementation

The first five items below are the pre-Bob laboratory (tag `baseline-pre-bob`). The fix and the new regression tests are IBM Bob's work.

- **Ticket Lab:** Laravel 13 on PHP 8.4+ (developed on PHP 8.5), SQLite, Blade, Tailwind CSS 4, Vite 8.
- **Journey telemetry:** `JourneyTracker` / `JourneyRecorder` write every funnel step to `journey_sessions` / `journey_events` and to a JSON Lines log. Outcomes (completed, abandoned, no_checkout, active) are derived. An `X-Journey-Id` header links responses to journeys.
- **Read model:** `ops:summary`, `ops:journeys --with-sequence`, `ops:journey <code>` and an admin `/ops` view.
- **Synthetic traffic:** a headless `SyntheticBrowser` drives the real routes, middleware, sessions and CSRF, in-process or over HTTP. `demo:reset` produces the same three-day history on every run.
- **Replay:** `demo:replay-checkout` runs a new visitor through share link → checkout → signup → payment, prints each stage as OBSERVED/MISSING, and exits 0 only when the purchase completes.
- **Fix (implemented by IBM Bob in Task 03):** a guard in `RegisterController` forcing the buyer role when a `PurchaseIntent` is active, plus a corrected default in `config/accounts.php` (commit `8c2cd31`).
- **Tests:** PHPUnit 12, with 48 tests / 191 assertions (up from 45 / 165), including three regression tests for the escaped journey.

## What Makes It Different

- Bob was **not told there was a bug**. It started from operational evidence and decided on its own that something was wrong.
- The verification target is the **user journey**, not only the test suite: a deterministic replay with a binary outcome that anyone can re-run.
- Bob **challenged its own plan**: it replaced the minimal config fix from Task 02 with a config-independent invariant in Task 03.
- **Independent verification** ran as a separate task with a no-edit constraint.
- The whole story is **reproducible from git**: two tags, four exported Bob tasks, one fix commit.

## Impact

The same pattern (a role, redirect or state bug that loses users while CI stays green) appears in signup funnels, checkouts, onboarding flows and permission systems. Giving an agent journey evidence moves it from "fix what I describe" to "find out what happened, prove it, fix it, and show the user journey now works". In this prototype the result is concrete: the replayed checkout-signup journey went from ABANDONED (no order) to COMPLETED (paid order), with three new regression tests guarding it. These results come from a synthetic lab, not from production traffic.

## Before vs After

| | Before | After |
|---|---|---|
| Role created by checkout signup | producer | buyer |
| Redirect after signup | `/producer/onboarding` | `/checkout/ai-builders-night-2026` |
| Next journey event | `producer_onboarding_viewed` | `checkout_resumed` |
| Journey outcome | ABANDONED | COMPLETED |
| Order / payment | none | paid |
| Tests | 45 passed | 48 passed, 191 assertions, 0 regressions |
| Explicit producer signup | onboarding | onboarding (unchanged) |

## Challenges

- **Designing a realistic defect.** It had to arise from three individually reasonable pieces of code, not an obvious typo, and the repository could contain no hints about it.
- **Deterministic synthetic traffic** through the real Laravel HTTP stack, in-process. This meant restoring framework state between inner requests and simulating time consistently.
- **Keeping the investigation honest.** The prompts were written so Bob had to establish evidence before reading code, and every later task could only rely on the exported record of earlier ones.
- **Reproducing the baseline.** A shared, optimized Composer autoloader can silently load the fixed code into a baseline worktree. The demo guide documents how to avoid this.

## What We Learned

- Structured journey telemetry (role, redirect target and expected destination on every step) is what made the anomaly obvious. Generic request logs would not have shown it as clearly.
- Asking the agent to re-validate its plan before editing produced a better fix than the plan itself.
- An independent verification task with a no-edit constraint is a cheap, credible way to build trust in agent-made changes.
- A deterministic replay with a binary exit code is a useful complement to unit tests. It checks the thing users actually experience.

## Future Work

These items are not implemented.

- Ingest OpenTelemetry traces, structured logs and product analytics as journey evidence.
- Detect journey anomalies automatically (funnel drops, role/route mismatches) and open a Bob task with an evidence package.
- Add support tickets as an additional signal source.
- Run journey replays in CI/CD as merge gates.
- After deployment, compare live journey outcomes before and after a change.

## Tech Stack

IBM Bob (Ask, Plan and Agent modes, subagents, skills) · PHP 8.4+/8.5 · Laravel 13 · SQLite · Blade · Tailwind CSS 4 · Vite 8 · PHPUnit 12 · JSON Lines telemetry · synthetic data only.

## GitHub URL

https://github.com/filpe19/journeyops

## Demo URL

https://journeyops.tickiton.com.br

## Video URL

[TBD — Video URL]

## Pitch Deck URL

[TBD — Pitch Deck URL]
