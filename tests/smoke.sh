#!/usr/bin/env bash
set -euo pipefail

# Route smoke tests for nicograef.com
#
# Boots the PHP dev server (the same entry point router.php provides in
# development; .htaccess covers the same routes in production), exercises
# every pretty-URL route, and always tears the server down on exit. Prints
# PASS/FAIL per check and exits non-zero if any check fails.
#
# Usage: bash tests/smoke.sh

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
HOST="127.0.0.1"
PORT="${SMOKE_PORT:-8099}"
BASE_URL="http://${HOST}:${PORT}"

SERVER_LOG="$(mktemp)"
BODY_FILE="$(mktemp)"
HEADERS_FILE="$(mktemp)"
SERVER_PID=""
FAILED=0

cleanup() {
    if [[ -n "$SERVER_PID" ]] && kill -0 "$SERVER_PID" 2>/dev/null; then
        kill "$SERVER_PID" 2>/dev/null || true
        wait "$SERVER_PID" 2>/dev/null || true
    fi
    rm -f "$SERVER_LOG" "$BODY_FILE" "$HEADERS_FILE"
}
trap cleanup EXIT

pass() { printf 'PASS: %s\n' "$1"; }
fail() {
    printf 'FAIL: %s\n' "$1"
    FAILED=1
}

# GET a path, optionally with an Accept-Language header, into BODY_FILE /
# HEADERS_FILE. Prints the HTTP status code. Never fails the script itself —
# a connection error yields status "000" so the caller's check simply fails.
fetch() {
    local path="$1"
    local accept_language="${2:-}"
    local header_args=()
    if [[ -n "$accept_language" ]]; then
        header_args=(-H "Accept-Language: ${accept_language}")
    fi
    # curl -w already prints "000" to stdout on a connection error; the `|| code=000`
    # only guards errexit (it overwrites the same value, so the status stays a single
    # clean token rather than a doubled "000\n000" in failure diagnostics).
    local code
    code=$(curl -s -o "$BODY_FILE" -D "$HEADERS_FILE" -w '%{http_code}' \
        "${header_args[@]}" "${BASE_URL}${path}" 2>>"$SERVER_LOG") || code="000"
    printf '%s' "$code"
}

cd "$ROOT_DIR"
php -S "${HOST}:${PORT}" -t public router.php >"$SERVER_LOG" 2>&1 &
SERVER_PID=$!

# Poll until the server answers, the timeout elapses, or the server process
# dies early (e.g. the port is already in use) — whichever comes first.
READY=0
for _ in $(seq 1 50); do
    if ! kill -0 "$SERVER_PID" 2>/dev/null; then
        break
    fi
    if curl -s -o /dev/null "${BASE_URL}/" 2>/dev/null; then
        READY=1
        break
    fi
    sleep 0.2
done

if [[ "$READY" -ne 1 ]]; then
    echo "FAIL: dev server did not become ready on ${BASE_URL}" >&2
    echo "--- server log ---" >&2
    cat "$SERVER_LOG" >&2
    exit 1
fi

# / — no Accept-Language => German
status=$(fetch "/")
if [[ "$status" == "200" ]] && grep -q '<html lang="de"' "$BODY_FILE"; then
    pass 'GET / (no Accept-Language) => 200, lang="de"'
else
    fail "GET / (no Accept-Language) => 200, lang=\"de\" (got status=$status)"
fi

# / — Accept-Language: en => English
status=$(fetch "/" "en")
if [[ "$status" == "200" ]] && grep -q '<html lang="en"' "$BODY_FILE"; then
    pass 'GET / (Accept-Language: en) => 200, lang="en"'
else
    fail "GET / (Accept-Language: en) => 200, lang=\"en\" (got status=$status)"
fi

# /cv — no Accept-Language => German
status=$(fetch "/cv")
if [[ "$status" == "200" ]] && grep -q '<html lang="de"' "$BODY_FILE"; then
    pass 'GET /cv (no Accept-Language) => 200, lang="de"'
else
    fail "GET /cv (no Accept-Language) => 200, lang=\"de\" (got status=$status)"
fi

# /cv — Accept-Language: en => English
status=$(fetch "/cv" "en")
if [[ "$status" == "200" ]] && grep -q '<html lang="en"' "$BODY_FILE"; then
    pass 'GET /cv (Accept-Language: en) => 200, lang="en"'
else
    fail "GET /cv (Accept-Language: en) => 200, lang=\"en\" (got status=$status)"
fi

# /articles — German only
status=$(fetch "/articles")
if [[ "$status" == "200" ]] && grep -q '<html lang="de"' "$BODY_FILE"; then
    pass 'GET /articles => 200, lang="de"'
else
    fail "GET /articles => 200, lang=\"de\" (got status=$status)"
fi

# /articles/{slug} with a fenced code block => BOTH highlight assets loaded
# (the CSS link from articles.php and the JS <script> from article.php). Assert
# both independently so a half-broken gate — one present, the other dropped —
# still fails.
status=$(fetch "/articles/anti-corruption-layer-erklaert")
if [[ "$status" == "200" ]] \
    && grep -q 'vendor/highlight\.css' "$BODY_FILE" \
    && grep -q 'vendor/highlight\.js' "$BODY_FILE"; then
    pass 'GET /articles/anti-corruption-layer-erklaert => 200, includes highlight.css + highlight.js'
else
    fail "GET /articles/anti-corruption-layer-erklaert => 200, includes highlight.css + highlight.js (got status=$status)"
fi

# /articles/{slug} without a code block => no highlight.js assets
status=$(fetch "/articles/was-ist-event-sourcing")
if [[ "$status" == "200" ]] && ! grep -q 'vendor/highlight' "$BODY_FILE"; then
    pass 'GET /articles/was-ist-event-sourcing => 200, no vendor/highlight'
else
    fail "GET /articles/was-ist-event-sourcing => 200, no vendor/highlight (got status=$status)"
fi

# /articles/{unknown-slug} => 404
status=$(fetch "/articles/does-not-exist")
if [[ "$status" == "404" ]]; then
    pass 'GET /articles/does-not-exist => 404'
else
    fail "GET /articles/does-not-exist => 404 (got status=$status)"
fi

# /sitemap.xml => 200, XML content type
status=$(fetch "/sitemap.xml")
if [[ "$status" == "200" ]] && grep -qi '^content-type:.*application/xml' "$HEADERS_FILE"; then
    pass 'GET /sitemap.xml => 200, Content-Type: application/xml'
else
    fail "GET /sitemap.xml => 200, Content-Type: application/xml (got status=$status)"
fi

# Unknown top-level path => 404
status=$(fetch "/this-path-does-not-exist")
if [[ "$status" == "404" ]]; then
    pass 'GET /this-path-does-not-exist => 404'
else
    fail "GET /this-path-does-not-exist => 404 (got status=$status)"
fi

if [[ "$FAILED" -ne 0 ]]; then
    echo "Smoke tests FAILED"
    exit 1
fi

echo "Smoke tests PASSED"
