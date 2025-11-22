# Docker Guide

This repo ships with a Docker-first workflow for both development and production images. The
`docker/` folder is now split by concern:

- PHP API: `docker/app/Dockerfile` with runtime helpers in `docker/app/entrypoints/`
- Dev Nginx: `docker/web/nginx.prod.conf`
- Prod Nginx: `docker/web/Dockerfile` + `docker/web/nginx.prod.conf`
- UI SPA: `docker/ui/Dockerfile` + `src/ui/nginx/templates/default.conf.template`
- Shared snippets: `docker/shared/nginx/*.conf` (drop-in includes as needed)

## Local Development (Compose)

- Dev stack: `docker-compose.yml` (bind mounts + hot reload). Bring it up with `docker compose up`.
- PHP tooling (artisan/pint/phpstan/tests): `docker-compose exec app <command>` for consistency.
- Nginx in dev reads `docker/nginx.conf`; storage is mounted to both `app` and `web`.

## Production Images

Targets (GHCR in examples):

- App (PHP-FPM): `ghcr.io/slightlyprivate/petcare-companion-app:{prod,latest,<branch>}`
- Web (Nginx reverse proxy): `ghcr.io/slightlyprivate/petcare-companion-web:{prod,latest,<branch>}`
- UI (static React bundle): `ghcr.io/slightlyprivate/petcare-companion-ui:{prod,latest,<branch>}`

### Build (multi-arch)

```bash
# App
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --target runner \
  --tag ghcr.io/slightlyprivate/petcare-companion-app:prod \
  --tag ghcr.io/slightlyprivate/petcare-companion-app:latest \
  --file docker/app/Dockerfile \
  .

# Web
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --tag ghcr.io/slightlyprivate/petcare-companion-web:prod \
  --tag ghcr.io/slightlyprivate/petcare-companion-web:latest \
  --file docker/web/Dockerfile \
  .

# UI
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --tag ghcr.io/slightlyprivate/petcare-companion-ui:prod \
  --tag ghcr.io/slightlyprivate/petcare-companion-ui:latest \
  --file docker/ui/Dockerfile \
  .
```

### Push / Pull

```bash
# Push (after login: docker login ghcr.io)
docker push ghcr.io/slightlyprivate/petcare-companion-app:prod
docker push ghcr.io/slightlyprivate/petcare-companion-web:prod
docker push ghcr.io/slightlyprivate/petcare-companion-ui:prod

# Pull prebuilt
docker pull ghcr.io/slightlyprivate/petcare-companion-app:prod
docker pull ghcr.io/slightlyprivate/petcare-companion-web:prod
docker pull ghcr.io/slightlyprivate/petcare-companion-ui:prod
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
