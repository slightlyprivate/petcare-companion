DEV_COMPOSE = docker-compose.yml
PROD_COMPOSE = docker-compose.prod.yml

.PHONY: up upd down seed migrate logs ps env bash pint stan restart test
.PHONY: build-app build-web build-ui build-all bake-all push-app push-web push-ui push-all prod-up prod-down

# =============================================================================
# Development Commands
# =============================================================================

up:
	docker compose -f $(DEV_COMPOSE) up

upd:
	docker compose -f $(DEV_COMPOSE) up -d

down:
	docker compose -f $(DEV_COMPOSE) down

restart:
	docker compose -f $(DEV_COMPOSE) restart

migrate:
	docker compose -f $(DEV_COMPOSE) exec app php artisan migrate

seed:
	docker compose -f $(DEV_COMPOSE) exec app php artisan migrate
	docker compose -f $(DEV_COMPOSE) exec app php artisan db:seed

pint:
	docker compose -f $(DEV_COMPOSE) exec app ./vendor/bin/pint

stan:
	docker compose -f $(DEV_COMPOSE) exec app composer stan

test:
	docker compose -f $(DEV_COMPOSE) exec app composer test

logs:
	docker compose -f $(DEV_COMPOSE) logs -f --tail=100

ps:
	docker compose -f $(DEV_COMPOSE) ps

env:
	docker compose -f $(DEV_COMPOSE) exec app cp .env.example .env
	docker compose -f $(DEV_COMPOSE) exec app composer install
	docker compose -f $(DEV_COMPOSE) exec app php artisan key:generate

bash:
	docker compose -f $(DEV_COMPOSE) exec app bash

# =============================================================================
# Production Build Commands
# =============================================================================

build-app:
	@echo "Building production app image with Bake (no push)..."
	docker buildx bake -f docker-bake.hcl app

build-web:
	@echo "Building production web image with Bake (no push)..."
	docker buildx bake -f docker-bake.hcl web

build-ui:
	@echo "Building production UI image with Bake (no push)..."
	docker buildx bake -f docker-bake.hcl ui

build-all: bake-all

bake-all:
	@echo "Building all production images with Bake (no push)..."
	docker buildx bake -f docker-bake.hcl all

push-app:
	@echo "Building and pushing app image with Bake..."
	docker buildx bake -f docker-bake.hcl --push app

push-web:
	@echo "Building and pushing web image with Bake..."
	docker buildx bake -f docker-bake.hcl --push web

push-ui:
	@echo "Building and pushing UI image with Bake..."
	docker buildx bake -f docker-bake.hcl --push ui

push-all:
	@echo "Building and pushing all images with Bake..."
	docker buildx bake -f docker-bake.hcl --push all

# =============================================================================
# Production Deployment Commands
# =============================================================================

prod-up:
	@echo "Starting production stack..."
	docker compose -f $(PROD_COMPOSE) up -d

prod-down:
	@echo "Stopping production stack..."
	docker compose -f $(PROD_COMPOSE) down

prod-logs:
	docker compose -f $(PROD_COMPOSE) logs -f --tail=100

prod-ps:
	docker compose -f $(PROD_COMPOSE) ps

# =============================================================================
# Image Management
# =============================================================================

image-sizes:
	@echo "App Image:"
	@docker manifest inspect ghcr.io/slightlyprivate/petcare-companion-app:prod | jq '.manifests[].platform, .manifests[].size'
	@echo ""
	@echo "Web Image:"
	@docker manifest inspect ghcr.io/slightlyprivate/petcare-companion-web:prod | jq '.manifests[].platform, .manifests[].size'
	@echo ""
	@echo "UI Image:"
	@docker manifest inspect ghcr.io/slightlyprivate/petcare-companion-ui:prod | jq '.manifests[].platform, .manifests[].size'

image-scan:
	@echo "Scanning images for vulnerabilities..."
	trivy image ghcr.io/slightlyprivate/petcare-companion-app:prod
	trivy image ghcr.io/slightlyprivate/petcare-companion-web:prod
	trivy image ghcr.io/slightlyprivate/petcare-companion-ui:prod

