#!/usr/bin/env bash
# Replays the "new visitor from a shared event link" checkout journey and
# prints what the telemetry recorded.
#
#   ./scripts/replay_checkout_journey.sh                          # in-process, no server needed
#   ./scripts/replay_checkout_journey.sh --url=http://127.0.0.1:8000   # against a running server
set -euo pipefail
cd "$(dirname "$0")/.."

php artisan demo:replay-checkout "$@"
