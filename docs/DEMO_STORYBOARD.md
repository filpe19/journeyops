# Demo Video — Storyboard and Source Material

This is factual source material and a recommended screen sequence for the demo video. It is **not** the final narration. Timings are a suggested shape for a video of about 4½ minutes. Every fact below can be traced to the repository.

## Available recordings and assets

The repository contains **no video recordings**. The visual evidence it does contain:

| Asset | Path | Shows |
|---|---|---|
| Task 01 screenshot | `bob_sessions/journeyops_task01_operational_analysis_summary.png` | Ask-mode task, its final recommendations, context 32.5k, 0.364 Bobcoins |
| Task 02 screenshot | `bob_sessions/journeyops_task02_remediation_plan_summary.png` | Plan-mode task, risk table, 0.538 Bobcoins |
| Task 03 screenshot | `bob_sessions/journeyops_task03_implementation_summary.png` | Agent-mode task, "All tasks completed! 10/10", "5 files changed", commit `8c2cd31` in terminal, 2.75 Bobcoins |
| Task 04 screenshot | `bob_sessions/journeyops_task04_final_verification_summary.png` | Agent-mode verification task, 9/10 → final audit report, 2.85 Bobcoins |
| Task exports | `bob_sessions/journeyops_task0N_*.md` | Full prompts, tool calls and final reports |

> **Privacy note for editing:** the Task 01 and Task 02 screenshots show the Bob *Settings → General* panel with the account email, plan and budget. Crop or blur that panel if you use these screenshots in the video.

Anything else has to be **screen-recorded fresh**:

| Source | Use it for |
|---|---|
| Live demo https://journeyops.tickiton.com.br (the fixed `main`) | Landing hero with the before/after trace, `/demo`, the fixed ticket journey, the order confirmation with *What the telemetry recorded* |
| Local `baseline-pre-bob` worktree ([DEMO_GUIDE.md §4](DEMO_GUIDE.md#4-reproduce-the-baseline-failure-optional)) | The broken journey ending on `/producer/onboarding` (the public demo never runs the baseline) |
| Local `main` signed in as the synthetic operator | The ops console (`/ops`) and a journey timeline. `/ops` is blocked on the public demo |

Screens on the live demo worth recording:

| Page | What's on screen |
|---|---|
| `/` hero | "The test suite was green. The user journey wasn't." Journey trace switching **Before IBM Bob** → **After IBM Bob**, and the 45 → 48 · ABANDONED → COMPLETED strip |
| `/#before-after` | Two cards: *Synthetic baseline before IBM Bob* (ABANDONED) vs *Verified after IBM Bob* (COMPLETED) |
| `/#workflow` | Four cards, Discover · Plan · Fix · Verify, each linking to its Bob task export |
| `/#architecture` | Evidence → IBM Bob → verification blocks |
| Event page | *AI Builders Night 2026* with a *JourneyOps demo scenario* badge and **Buy ticket** |
| Sign-in wall | "Sign in or create an account." with a *Checkout in progress* card |
| Registration | "Create your account to continue checkout." with no account-type selector and the synthetic-identity notice |
| Checkout | "Review and pay", marked *Simulated checkout*, **Pay $49.00** |
| Confirmation | "Purchase complete" plus *What the telemetry recorded*: COMPLETED, `checkout_resumed` / `order_created` / `payment_completed` recorded, `user_role=buyer` |

## Sequence

### 00:00 — Hook

- **On screen:** the live landing hero. The journey trace starts on **Before IBM Bob** (`producer` → `/producer/onboarding` → ABANDONED). Optionally cut to a terminal: `php artisan test` at `baseline-pre-bob` → `45 passed`.
- **Facts:** "The test suite was green. The user journey wasn't." The failure is the lab's seeded scenario, prepared before Bob (tag `baseline-pre-bob`). Don't present it as a real customer incident.

### 00:20 — Problem

- **On screen:** the landing's *Start from what the user experienced* section (three cards: wrong redirect, unexpected role, silent funnel loss).
- **Facts:** Many failures are wrong redirects, role assignments and state-dependent flows. They don't crash and don't fail tests. Most coding agents start after someone has already worked out the bug.

### 00:45 — Broken journey

- **On screen:** recorded in the **local baseline worktree**: `/events/ai-builders-night-2026?ref=share` → **Buy ticket** → sign-in wall → **Create an account** → lands on `/producer/onboarding` ("Set up your organizer profile") instead of checkout. Alternative: the landing's before/after cards.
- Optionally show `php artisan ops:journeys --with-sequence --source=event_share` with the `abandoned` row.
- **Facts:** `signup_completed.user_role = producer`, `target_route = /producer/onboarding`, no order.

### 01:15 — Bob Task 01: Discover

- **On screen:** Task 01 prompt (export top), then the "Suspicious or Inconsistent Behavior" section of the export, then the Task 01 screenshot.
- **Facts:** Generic prompt that says not to assume a bug; Ask mode; subagents read the telemetry; 16 journeys and 7 paid orders summarised; `JRN-721B2F18` flagged; code traced to `register.blade.php`, `RegisterAccount.php`, `config/accounts.php` and `PostAuthenticationRedirect.php`.

### 01:50 — Bob Task 02: Plan

- **On screen:** "Remediation Options" (A–D) in the export; Task 02 screenshot (Plan mode).
- **Facts:** Four options compared; option D rejected because it weakens producer onboarding; regression test plan and acceptance criteria defined.

### 02:15 — Bob Task 03: Fix

- **On screen:** Task 03 "Implementation Decision" section; `git show 8c2cd31 -- app config` (a 7-line guard plus a 1-line default change); Task 03 screenshot.
- **Facts:** Bob re-validated the plan and found that a config-only fix still fails with `ACCOUNT_DEFAULT_TYPE=producer`. The fix has two layers: a buyer guard when a `PurchaseIntent` is active, and a `buyer` default.

### 03:00 — Tests

- **On screen:** `php artisan test` at `bob-final-verified` → `48 passed (191 assertions)`. Optionally show the three new test names. (`main` shows 51 / 206: the same 48 plus 3 tests for the demo pages.)
- **Facts:** 45 → 48 tests, zero regressions.

### 03:20 — Replay

- **On screen:** the **live demo**: landing → **Launch live demo** → event → **Buy ticket** → **Create an account** (*Create your account to continue checkout*) → back on checkout as a buyer → **Pay $49.00** → *Purchase complete* with *What the telemetry recorded* showing **COMPLETED**. Optionally add the terminal replay (`demo:replay-checkout`: all stages OBSERVED, exit 0) and the local ops console timeline of the same kind of journey.
- **Facts:** `signup_completed.user_role = buyer`, `target_route = /checkout/ai-builders-night-2026`, `checkout_resumed`, `payment_completed`.

### 03:50 — Bob Task 04: Verify

- **On screen:** Task 04 "Final Verification Result" (✅ VERIFIED) and the "Before vs After Confirmation" table; Task 04 screenshot.
- **Facts:** Separate task, not allowed to edit; replay over HTTP (`JRN-9091D539`, COMPLETED); producer journey `JRN-1FB32E21` still goes to onboarding; git clean before and after.

### 04:15 — Architecture and conclusion

- **On screen:** the landing's *Operational evidence → verified fix* architecture block (or the Mermaid diagrams in `docs/ARCHITECTURE.md`), the *Every claim is in the repository* evidence section, the GitHub repo with tags `baseline-pre-bob` and `bob-final-verified`, and the live URL.
- **Facts:** JourneyOps gives Bob evidence of what happened to the user. Future items (OpenTelemetry, CI replay gates, deployment verification) must be labelled **future**.

## Proof checklist for the editor

- [ ] **Baseline behaviour:** 45 tests green *and* replay `ABANDONED` / user lands on `/producer/onboarding`
- [ ] **Task 01 discovery:** generic prompt visible; anomalous journey `JRN-721B2F18` named
- [ ] **Root cause:** the four code locations (view, action, config, redirect)
- [ ] **Fix:** commit `8c2cd31` diff (controller guard + config default)
- [ ] **48 tests:** `48 passed (191 assertions)`
- [ ] **Completed replay:** all stages OBSERVED, `User role: buyer`, `Outcome: COMPLETED`
- [ ] **Task 04 verification:** "✅ VERIFIED" and no-edit constraint visible
- [ ] **Architecture:** diagram on screen
- [ ] **Live demo:** https://journeyops.tickiton.com.br visible, with the confirmation page showing COMPLETED from the telemetry
- [ ] **Public GitHub:** repository URL and the two tags visible
- [ ] Account email and budget in the Bob settings panel cropped or blurred
