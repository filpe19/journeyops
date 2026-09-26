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

Anything else has to be **screen-recorded fresh** from a local run (see [DEMO_GUIDE.md](DEMO_GUIDE.md)).

## Sequence

### 00:00 — Hook

- **On screen:** terminal, `php artisan test` at `baseline-pre-bob` → `45 passed`. Cut to the baseline `demo:replay-checkout` output ending in `Outcome: ABANDONED`.
- **Facts:** "The test suite was green. The user journey wasn't."

### 00:20 — Problem

- **On screen:** simple title card or README hero.
- **Facts:** Many failures are wrong redirects, role assignments and state-dependent flows. They don't crash and don't fail tests. Most coding agents start after someone has already worked out the bug.

### 00:45 — Broken journey

- **On screen:** browser at `/events/ai-builders-night-2026?ref=share` → **Buy ticket** → sign-in wall → **Create an account** → lands on `/producer/onboarding` ("Set up your organizer profile") instead of checkout. Record this in the baseline worktree.
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

- **On screen:** `php artisan test` on `main` → `48 passed (191 assertions)`. Optionally show the three new test names.
- **Facts:** 45 → 48 tests, zero regressions.

### 03:20 — Replay

- **On screen:** `php artisan demo:reset --force && php artisan demo:replay-checkout` on `main`, with all ten stages OBSERVED, `User role: buyer`, `Outcome: COMPLETED`, exit code 0. Optionally repeat the browser flow and land on the order confirmation page.
- **Facts:** `signup_completed.user_role = buyer`, `target_route = /checkout/ai-builders-night-2026`, `checkout_resumed`, `payment_completed`.

### 03:50 — Bob Task 04: Verify

- **On screen:** Task 04 "Final Verification Result" (✅ VERIFIED) and the "Before vs After Confirmation" table; Task 04 screenshot.
- **Facts:** Separate task, not allowed to edit; replay over HTTP (`JRN-9091D539`, COMPLETED); producer journey `JRN-1FB32E21` still goes to onboarding; git clean before and after.

### 04:15 — Architecture and conclusion

- **On screen:** README Mermaid architecture diagram and agent workflow diagram from `docs/ARCHITECTURE.md`; the public GitHub repo page with tags `baseline-pre-bob` and `bob-final-verified`.
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
- [ ] **Public GitHub:** repository URL and the two tags visible
- [ ] Account email and budget in the Bob settings panel cropped or blurred
