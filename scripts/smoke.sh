#!/usr/bin/env bash
# HTTP smoke test against a running instance (default http://127.0.0.1:8000).
set -euo pipefail
BASE_URL="${1:-http://127.0.0.1:8000}"

check() {
  local path="$1" expected="$2"
  local code
  code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE_URL$path")
  if [ "$code" = "$expected" ]; then
    printf '%-45s %s OK\n' "$path" "$code"
  else
    printf '%-45s %s (expected %s) FAIL\n' "$path" "$code" "$expected"
    exit 1
  fi
}

check /health 200
check / 200
check /events/ai-builders-night-2026 200
check /login 200
check /register 200
check /ops 302
curl -s "$BASE_URL/health"; echo
