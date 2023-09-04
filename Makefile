# Greatly inspired by https://github.com/dunglas/symfony-docker Makefile

.DEFAULT_GOAL = help
.PHONY: init build up start composer vendor test test-with-coverage infection

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'


init: start vendor ## Build, start the container and install vendors

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	docker compose build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	docker compose up --detach

start: build up ## Build and start the containers

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, examples: make composer c='require --dev approvals/approval-tests:dev-Main', make composer c='require --dev infection/infection'
	@$(eval c ?=)
	docker compose exec php composer $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-progress --no-interaction
vendor: composer

## —— Tools 🔧 —————————————————————————————————————————————————————————————————

test: ## Launch PHPUnit
	docker-compose run php vendor/bin/phpunit

test-with-coverage: ## Launch PHPUnit and generate a coverage
	docker-compose exec php vendor/bin/phpunit --coverage-html coverage
