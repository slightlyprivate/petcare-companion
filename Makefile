VERSION := $(shell cat VERSION)
DEFAULT_BRANCH ?= main
CURRENT_BRANCH := $(shell git rev-parse --abbrev-ref HEAD 2>/dev/null)
IS_DEFAULT_BRANCH := $(if $(filter $(DEFAULT_BRANCH),$(CURRENT_BRANCH)),1,0)

APP_TAGS := ghcr.io/slightlyprivate/petcare-companion-app:$(VERSION)
WEB_TAGS := ghcr.io/slightlyprivate/petcare-companion-web:$(VERSION)
UI_TAGS := ghcr.io/slightlyprivate/petcare-companion-ui:$(VERSION)

ifeq ($(IS_DEFAULT_BRANCH),1)
APP_TAGS := $(APP_TAGS),ghcr.io/slightlyprivate/petcare-companion-app:latest
WEB_TAGS := $(WEB_TAGS),ghcr.io/slightlyprivate/petcare-companion-web:latest
UI_TAGS := $(UI_TAGS),ghcr.io/slightlyprivate/petcare-companion-ui:latest
endif

BAKE_FILE := docker-bake.hcl
BAKE_TAGS := --set app.tags=$(APP_TAGS) --set web.tags=$(WEB_TAGS) --set ui.tags=$(UI_TAGS)

DEV_COMPOSE = docker-compose.yml
PROD_COMPOSE = docker-compose.prod.yml

.PHONY: up upd down seed migrate logs ps env bash pint stan restart test bump-version
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
	docker buildx bake -f $(BAKE_FILE) --set app.tags=$(APP_TAGS) app

build-web:
	@echo "Building production web image with Bake (no push)..."
	docker buildx bake -f $(BAKE_FILE) --set web.tags=$(WEB_TAGS) web

build-ui:
	@echo "Building production UI image with Bake (no push)..."
	docker buildx bake -f $(BAKE_FILE) --set ui.tags=$(UI_TAGS) ui

build-all: bake-all

bake-all:
	@echo "Building all production images with Bake (no push)..."
	docker buildx bake -f $(BAKE_FILE) $(BAKE_TAGS) all

push-app:
	@echo "Building and pushing app image with Bake..."
	docker buildx bake -f $(BAKE_FILE) --set app.tags=$(APP_TAGS) --push app

push-web:
	@echo "Building and pushing web image with Bake..."
	docker buildx bake -f $(BAKE_FILE) --set web.tags=$(WEB_TAGS) --push web

push-ui:
	@echo "Building and pushing UI image with Bake..."
	docker buildx bake -f $(BAKE_FILE) --set ui.tags=$(UI_TAGS) --push ui

push-all:
	@echo "Building and pushing all images with Bake..."
	docker buildx bake -f $(BAKE_FILE) $(BAKE_TAGS) --push all

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

bump-version:
	@echo "Usage: make bump-version PART=patch|minor|major"
	@current=$$(cat VERSION); \
	IFS=. read -r major minor patch <<<"$$current"; \
	case "$(PART)" in \
		major) major=$$((major+1)); minor=0; patch=0 ;; \
		minor) minor=$$((minor+1)); patch=0 ;; \
		patch) patch=$$((patch+1)) ;; \
		*) echo "Invalid PART. Use patch, minor, or major."; exit 1 ;; \
	esac; \
	new="$$major.$$minor.$$patch"; \
	echo $$new > VERSION; \
	echo "Bumped version to $$new"

