# Pitch Deck — Source Content

This is the source content for a five-slide deck, not the final slides. Each slide lists its headline, on-slide content, a suggested visual and speaker notes. All numbers come from the repository and the IBM Bob task exports.

---

## Slide 1 — JourneyOps

**Headline:** *The test suite was green. The user journey wasn't.*

**On slide**

- JourneyOps: from broken user journeys to verified fixes with IBM Bob
- Tests can pass while users still fail
- In our controlled lab: 45 / 45 tests passing, and every new visitor who signed up at checkout lost their purchase

**Visual:** a green CI badge next to a journey that ends in a red "ABANDONED".

**Speaker notes:** Redirect mistakes, wrong role assignments and state-dependent flows don't crash and don't fail tests. Someone has to work out what happened to the user first. Most coding agents start after that point.

---

## Slide 2 — The Missing Context

**Headline:** Bob starts from what happened, not from a bug description.

**On slide**

```
Application state
      +
Journey telemetry  (role · route · redirect target · outcome, on every step)
      +
Source code & tests
      ↓
   IBM Bob
```

- No bug ticket. The prompt was: *"Is the ticket purchasing experience operating normally? Do not assume a bug exists."*

**Visual:** three input streams converging on the Bob logo.

**Speaker notes:** Telemetry is recorded by the application itself (SQLite + JSON Lines), and ops commands give a read model. Bob reads it the way a production engineer would.

---

## Slide 3 — Bob Workflow

**Headline:** Four tasks, each checkable by the next.

| Task 01 | Task 02 | Task 03 | Task 04 |
|---|---|---|---|
| **Discover** | **Plan** | **Fix** | **Verify** |
| Ask mode + subagents | Plan mode + `create-plan` skill | Agent mode | Agent mode, no edits |
| Found 1 anomalous journey among 16 and traced it to code | 4 options compared, tests and acceptance criteria defined | Two-layer fix, 3 regression tests, replay | Tests, HTTP replay, telemetry, git state: VERIFIED |

**Provenance strip (small, under the timeline):**
`BEFORE BOB: controlled lab + seeded failure scenario (baseline-pre-bob)` → `WITH BOB: discover → plan → fix → verify` → `AFTER: verified completed journey (bob-final-verified)`

**Visual:** four-step horizontal timeline, one screenshot thumbnail from `bob_sessions/` per task.

**Speaker notes:** In Task 03 Bob rejected the minimal plan from Task 02: a config-only fix would still break if a deployment set `ACCOUNT_DEFAULT_TYPE=producer`. It enforced the invariant in code instead.

---

## Slide 4 — Before vs After

**Headline:** ABANDONED → COMPLETED

| Before | After |
|---|---|
| signup → **producer** | signup → **buyer** |
| → producer onboarding | → checkout resumed |
| → abandoned | → paid |
| no order | order + payment completed |

- Tests: **45 → 48**, **191 assertions**, **zero regressions**
- Producer signup still goes to onboarding (unchanged)

**Visual:** two journey timelines side by side (red vs. green), taken from `demo:replay-checkout` output.

**Speaker notes:** Before and after are pinned by git tags (`baseline-pre-bob`, `bob-final-verified`). Anyone can re-run the replay: exit code 1 before, 0 after.

---

## Slide 5 — Why It Matters / Future

**Headline:** Agents should verify the journey, not just the diff.

**Implemented today (prototype):** journey telemetry, an ops read model, deterministic replay, four-stage Bob workflow, and evidence committed to git.

**FUTURE (not implemented):**

- FUTURE: OpenTelemetry traces as journey evidence
- FUTURE: structured logs and product analytics
- FUTURE: support signals (tickets, complaints)
- FUTURE: automatic anomaly detection that opens a Bob task
- FUTURE: journey replay as a CI/CD gate
- FUTURE: deployment verification that compares live journeys before and after

**Closing line:** *Most coding agents start when a developer already knows what to fix. JourneyOps starts one step earlier.*

**Visual:** today's loop in solid lines; future integrations in dashed outlines labelled FUTURE.
