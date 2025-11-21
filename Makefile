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
		.

build-all: build-app build-web
	@echo "All production images built successfully"

push-app:
	@echo "Pushing app image to registry..."
	docker push ghcr.io/slightlyprivate/petcare-companion-app:prod
	docker push ghcr.io/slightlyprivate/petcare-companion-app:latest

push-web:
	@echo "Pushing web image to registry..."
	docker push ghcr.io/slightlyprivate/petcare-companion-web:prod
	docker push ghcr.io/slightlyprivate/petcare-companion-web:latest

push-all: push-app push-web
	@echo "All images pushed to registry"

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
	@echo "Checking image sizes..."
	@echo ""
	@echo "App Image:"
	@docker images ghcr.io/slightlyprivate/petcare-companion-app:prod --format "  Size: {{.Size}}"
	@echo ""
	@echo "Web Image:"
	@docker images ghcr.io/slightlyprivate/petcare-companion-web:prod --format "  Size: {{.Size}}"
	@echo ""
	@echo "UI Image:"
	@docker images ghcr.io/slightlyprivate/petcare-companion-ui:prod --format "  Size: {{.Size}}"

image-scan:
	@echo "Scanning images for vulnerabilities..."
	@command -v trivy >/dev/null 2>&1 || { echo "Trivy not installed. Install from https://github.com/aquasecurity/trivy"; exit 1; }
	trivy image ghcr.io/slightlyprivate/petcare-companion-app:prod
	trivy image ghcr.io/slightlyprivate/petcare-companion-web:prod
