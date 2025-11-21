DEV_COMPOSE = docker-compose.yml
PROD_COMPOSE = docker-compose.prod.yml

.PHONY: up upd down seed migrate logs ps env bash pint stan restart test
.PHONY: build-app build-web build-all push-app push-web push-all prod-up prod-down

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
	@echo "Building production app image..."
	docker buildx build \
    --platform linux/amd64,linux/arm64 \
    --target runner \
    --tag ghcr.io/slightlyprivate/petcare-companion-app:prod \
    --tag ghcr.io/slightlyprivate/petcare-companion-app:latest \
    --file docker/app/Dockerfile \
    --cache-from type=registry,ref=ghcr.io/slightlyprivate/petcare-companion-app:buildcache \
    --cache-to type=registry,ref=ghcr.io/slightlyprivate/petcare-companion-app:buildcache,mode=max \
		--push \
    .

build-web:
	@echo "Building production web image..."
	docker buildx build \
		--platform linux/amd64,linux/arm64 \
		--tag ghcr.io/slightlyprivate/petcare-companion-web:prod \
		--tag ghcr.io/slightlyprivate/petcare-companion-web:latest \
		--file docker/web/Dockerfile \
		--cache-from type=registry,ref=ghcr.io/slightlyprivate/petcare-companion-web:buildcache \
		--cache-to type=registry,ref=ghcr.io/slightlyprivate/petcare-companion-web:buildcache,mode=max \
		--push \
		.

build-ui:
	@echo "Building production UI image..."
	docker buildx build \
		--platform linux/amd64,linux/arm64 \
		--tag ghcr.io/slightlyprivate/petcare-companion-ui:prod \
		--tag ghcr.io/slightlyprivate/petcare-companion-ui:latest \
		--file docker/ui/Dockerfile \
		--cache-from type=registry,ref=ghcr.io/slightlyprivate/petcare-companion-ui:buildcache \
		--cache-to type=registry,ref=ghcr.io/slightlyprivate/petcare-companion-ui:buildcache,mode=max \
		--push \
		.

build-all: build-app build-web build-ui
	@echo "All production images built successfully"

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

