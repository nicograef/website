# Makefile — nicograef.com (vanilla PHP, no build step)
# Run `make help` for the full list.

# ── Local Development ──

.PHONY: dev

dev:           ## Start the PHP dev server on :8080
	php -S 0.0.0.0:8080 -t public router.php

# ── Code Quality (mirrors .github/workflows/deploy.yml lint job) ──

.PHONY: lint stan smoke check

lint:          ## Syntax-lint all user PHP files on the local PHP
	find public router.php -name '*.php' -not -path 'public/vendor/*' -print0 \
		| xargs -0 -n1 -P4 php -l

stan:          ## Run PHPStan static analysis
	php phpstan.phar analyse --no-progress

smoke:         ## Boot the dev server and smoke-test every pretty-URL route
	bash tests/smoke.sh

check: lint stan smoke  ## Run the full CI quality gate (lint + PHPStan + smoke)

# ── Performance Audit (local-only, not CI — see AGENTS.md) ──

.PHONY: lighthouse

lighthouse:    ## Run a local Lighthouse audit (perf/a11y/SEO) against key routes — reports in tmp/lighthouse/
	mkdir -p tmp/lighthouse
	HOST=127.0.0.1; PORT="$${LIGHTHOUSE_PORT:-8098}"; \
	php -S "$$HOST:$$PORT" -t public router.php >tmp/lighthouse/server.log 2>&1 & \
	SERVER_PID=$$!; \
	trap 'kill $$SERVER_PID 2>/dev/null || true' EXIT INT TERM; \
	READY=0; \
	for i in $$(seq 1 50); do \
		if curl -s -o /dev/null "http://$$HOST:$$PORT/"; then READY=1; break; fi; \
		sleep 0.2; \
	done; \
	if [ "$$READY" -ne 1 ]; then echo "lighthouse: dev server did not start on http://$$HOST:$$PORT" >&2; exit 1; fi; \
	FAILED=0; \
	for ENTRY in "/=home" "/cv=cv" "/articles=articles" "/articles/anti-corruption-layer-erklaert=article"; do \
		ROUTE="$${ENTRY%%=*}"; NAME="$${ENTRY##*=}"; \
		echo "==> lighthouse $$ROUTE"; \
		npx --yes lighthouse "http://$$HOST:$$PORT$$ROUTE" \
			--output html --output json \
			--output-path "tmp/lighthouse/$$NAME" \
			--chrome-flags="--headless=new --no-sandbox" \
			--quiet || FAILED=1; \
	done; \
	exit $$FAILED

# ── Utilities ──

.PHONY: help

help:          ## Show this help
	@grep -hE '^[a-zA-Z_-]+:.*##' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "} {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help
