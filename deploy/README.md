# Deployment Configurations

This directory contains Docker Compose configurations for different deployment environments of the
PetCare Companion application.

## Directory Structure

- **`development/`** - Development environment with Traefik routing and external networks
- **`staging/`** - Staging environment for testing develop branch changes
- **`production/`** - Production-ready configuration with security hardening

## Environment Overview

### Development (`development/`)

**Purpose:** Remote development environment with HTTPS routing via Traefik

**Key Features:**

- Uses `:develop` tagged images from GHCR
- Traefik proxy integration with automatic SSL via Cloudflare
- External networks: `shared-db-petcare`, `shared-cache-petcare`, `traefik-proxy`
- Persistent storage at `/mnt/data/appdata/petcare-storage`
- Promtail log collection enabled
- Services: `app`, `web`, `ui`, `pwa`, `worker`, `scheduler`

**Hostnames:**

- Web: `web.develop.petcare.ubuntu.slightlyprivate.com`
- UI: `ui.develop.petcare.ubuntu.slightlyprivate.com`
- PWA: `pwa.develop.petcare.ubuntu.slightlyprivate.com`

**Setup:**

```bash
cd deploy/development
cp .env.example .env
# Configure environment variables
docker compose up -d
```

### Staging (`staging/`)

**Purpose:** Pre-production testing of the `develop` branch

**Key Features:**

- Uses `:develop` tagged images from GHCR
- Shared external networks for database and cache
- Port-based access (no Traefik)
- Shared storage volume between services
- Services: `app`, `web`, `ui`, `pwa`

**Ports:**

- Web: `9080`
- PWA: `9081`
- UI: `9082`

**Setup:**

```bash
cd deploy/staging
cp .env.example .env.staging
# Configure environment variables
docker compose up -d
```

### Production (`production/`)

**Purpose:** Production deployment with security best practices

**Key Features:**

- Uses `:prod` tagged images from GHCR
- Read-only filesystems with minimal tmpfs mounts
- Runs as non-root user (`www-data`)
- Comprehensive healthchecks with start periods
- Isolated frontend/backend networks
- Includes queue worker with memory limits
- Services: `app`, `web`, `worker`, `ui`, `pwa`

**Ports:**

- Web: `127.0.0.1:8080`
- PWA: `127.0.0.1:8081`
- UI: `127.0.0.1:8082`

**Setup:**

```bash
cd deploy/production
cp .env.example .env
# Configure all required secrets and external service endpoints
docker compose pull
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
```

## Image Tags

- **Development:** `ghcr.io/slightlyprivate/petcare-companion-{service}:develop`
- **Staging:** `ghcr.io/slightlyprivate/petcare-companion-{service}:develop`
- **Production:** `ghcr.io/slightlyprivate/petcare-companion-{service}:prod`

Services include: `app`, `web`, `ui`, `pwa`

## Common Services

All environments deploy:

- **app** - Laravel backend (PHP-FPM)
- **web** - Nginx reverse proxy to PHP-FPM
- **ui** - React admin interface
- **pwa** - Progressive Web App for end users

Development and staging include:

- **worker** - Queue worker for asynchronous jobs
- **scheduler** - Laravel task scheduler (development only)

## External Dependencies

### Development

- Database: Shared MySQL via `shared-db-petcare` network
- Cache: Shared Redis via `shared-cache-petcare` network
- Proxy: Traefik via `traefik-proxy` network

### Staging

- Database: External MySQL via `shared-db` network
- Cache: External Redis via `shared-cache` network

### Production

- Database: External MySQL (configure in `.env`)
- Cache: External Redis (configure in `.env`)
- Object Storage: Optional (S3-compatible)

## Quick Start

1. Choose your environment directory
2. Copy `.env.example` to `.env` (or `.env.staging` for staging)
3. Generate application key: `docker compose run --rm app php artisan key:generate --show`
4. Configure database and cache endpoints
5. Pull images: `docker compose pull`
6. Start services: `docker compose up -d`
7. Run migrations: `docker compose exec app php artisan migrate --force`

## Documentation

- [Staging Environment Details](../docs/staging-environment.md)
- [Production Deployment Guide](../docs/production-deployment.md)
- [CI/CD Setup](../docs/CI_CD_SETUP.md)

## Notes

- The root `docker-compose.yml` is for **local development only** with bind mounts and build
  contexts
- All deployment environments pull pre-built images from GHCR
- Production uses read-only filesystems and runs as non-root for security
- Development environment requires Traefik and external networks to be pre-configured
