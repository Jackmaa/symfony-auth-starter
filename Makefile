.PHONY: help install test phpstan cs-check cs-fix

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	composer install

test: ## Run PHPUnit tests
	vendor/bin/phpunit

phpstan: ## Run PHPStan static analysis
	vendor/bin/phpstan analyse

cs-check: ## Check coding standards
	@echo "Code style checking not yet configured"
	@echo "Consider adding PHP-CS-Fixer or PHP_CodeSniffer"

cs-fix: ## Fix coding standards
	@echo "Code style fixing not yet configured"
	@echo "Consider adding PHP-CS-Fixer"

validate: ## Validate composer.json
	composer validate --strict

all: install validate phpstan test ## Run all checks
