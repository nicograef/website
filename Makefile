# Makefile — nicograef.com (vanilla PHP, no build step)
# Run `make help` for the full list.

# ── Local Development ──

.PHONY: dev

dev:           ## Start the PHP dev server on :8080
	php -S 0.0.0.0:8080 -t public router.php

# ── Code Quality (mirrors .github/workflows/deploy.yml lint job) ──

.PHONY: lint stan check

lint:          ## Syntax-lint all user PHP files on the local PHP
	find public router.php -name '*.php' -not -path 'public/vendor/*' -print0 \
		| xargs -0 -n1 -P4 php -l

stan:          ## Run PHPStan static analysis
	php phpstan.phar analyse --no-progress

check: lint stan  ## Run the full CI quality gate (lint + PHPStan)

# ── Utilities ──

.PHONY: help

help:          ## Show this help
	@grep -hE '^[a-zA-Z_-]+:.*##' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "} {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help
