# Docker Guide

This repo ships with a Docker-first workflow for both development and production images. The
`docker/` folder is now split by concern:

- PHP API: `docker/app/Dockerfile` with runtime helpers in `docker/app/entrypoints/`
- Dev Nginx: `docker/web/nginx.prod.conf`
- Prod Nginx: `docker/web/Dockerfile` + `docker/web/nginx.prod.conf`
- UI SPA (account/billing): `docker/ui/Dockerfile` + `src/ui/nginx/templates/default.conf.template`
- PWA Experience UI: `docker/pwa/Dockerfile` + `src/pwa/nginx/templates/default.conf.template`
- Shared snippets: `docker/shared/nginx/*.conf` (drop-in includes as needed)

## Local Development (Compose)

- Dev stack: `docker-compose.yml` (bind mounts + hot reload). Bring it up with `docker compose up`.
- PHP tooling (artisan/pint/phpstan/tests): `docker-compose exec app <command>` for consistency.
- Nginx in dev reads `docker/nginx.conf`; storage is mounted to both `app` and `web`.

## Production Images

Targets (GHCR in examples):

- App (PHP-FPM): `ghcr.io/slightlyprivate/petcare-companion-app:{staging-<ver>,release-<ver>,dev-<sha>}`
- Web (Nginx reverse proxy): `ghcr.io/slightlyprivate/petcare-companion-web:{staging-<ver>,release-<ver>,dev-<sha>}`
- UI (static React bundle): `ghcr.io/slightlyprivate/petcare-companion-ui:{staging-<ver>,release-<ver>,dev-<sha>}`
- PWA Experience UI: `ghcr.io/slightlyprivate/petcare-companion-pwa:{staging-<ver>,release-<ver>,dev-<sha>}`

### Build (multi-arch)

```bash
# App
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --target runner \
  --tag ghcr.io/slightlyprivate/petcare-companion-app:staging-1.2.3 \
  --tag ghcr.io/slightlyprivate/petcare-companion-app:release-1.2.3 \
  --file docker/app/Dockerfile \
  .

# Web
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --tag ghcr.io/slightlyprivate/petcare-companion-web:staging-1.2.3 \
  --tag ghcr.io/slightlyprivate/petcare-companion-web:release-1.2.3 \
  --file docker/web/Dockerfile \
  .

# UI
docker buildx build \
  --platform linux/amd64 \
  --tag ghcr.io/slightlyprivate/petcare-companion-ui:staging-1.2.3 \
  --tag ghcr.io/slightlyprivate/petcare-companion-ui:release-1.2.3 \
  --file docker/ui/Dockerfile \
  .

# PWA
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --tag ghcr.io/slightlyprivate/petcare-companion-pwa:staging-1.2.3 \
  --tag ghcr.io/slightlyprivate/petcare-companion-pwa:release-1.2.3 \
  --file docker/pwa/Dockerfile \
  .
```

### Push / Pull

```bash
# Push (after login: docker login ghcr.io)
docker push ghcr.io/slightlyprivate/petcare-companion-app:release-1.2.3
docker push ghcr.io/slightlyprivate/petcare-companion-web:release-1.2.3
docker push ghcr.io/slightlyprivate/petcare-companion-ui:release-1.2.3
docker push ghcr.io/slightlyprivate/petcare-companion-pwa:release-1.2.3

# Pull prebuilt
docker pull ghcr.io/slightlyprivate/petcare-companion-app:release-1.2.3
docker pull ghcr.io/slightlyprivate/petcare-companion-web:release-1.2.3
docker pull ghcr.io/slightlyprivate/petcare-companion-ui:release-1.2.3
docker pull ghcr.io/slightlyprivate/petcare-companion-pwa:release-1.2.3
```

`make build-app`, `make build-web`, and `make build-all` wrap the same buildx flows (see
`Makefile`). CI mirrors this via `.github/workflows/build-*-image.yml`.

## Production Run (Compose)

- Use `docker-compose.prod.yml` to deploy prebuilt images (no bind mounts). Pull with
  `docker compose -f docker-compose.prod.yml pull`.
- Mount or provision the `storage` volume before starting the stack.
- Run post-deploy tasks:

```bash
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

- The UI image renders its Nginx config from a template at runtime. Set `API_BASE_URL` on the UI
  service (e.g., `http://web`) so `/api` requests proxy to the Laravel Nginx service.

Health endpoints:

- Web: `http://<host>:8080/health` (or behind HTTPS)
- Horizon/queues: healthchecks are baked into the Compose definitions.
