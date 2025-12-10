# Deployment Guide

## Production Environment

This guide captures everything required to run the PetCare Companion stack in production. It assumes
you are deploying the containers defined in `deploy/prod/docker-compose.yml` and are pulling images
from GHCR (`ghcr.io/slightlyprivate/petcare-companion-*`).

### Prerequisites

- Access to the production secrets manager (APP_KEY, DB credentials, API keys, Stripe, mail, etc.).
- Managed MySQL and Redis instances reachable from the Docker host.
- Docker Compose v2 on the target host plus permissions to create Docker volumes.
- HTTPS termination in front of the `web`/`ui` containers (reverse proxy, load balancer, or cloud
  ingress) so Sanctum cookies remain secure.

### Environment Variable Requirements

Start with `src/.env.production.example`. The table below lists the minimum values you must set
before booting the stack:

| Variable                                                          | Required        | Purpose / Notes                                                         |
| ----------------------------------------------------------------- | --------------- | ----------------------------------------------------------------------- |
| `APP_ENV`, `APP_DEBUG`                                            | Yes             | Use `production` / `false` for optimized caches + logging.              |
| `APP_URL`, `FRONTEND_URL`                                         | Yes             | Public HTTPS URLs used in links, queued jobs, and mailers.              |
| `APP_KEY`                                                         | Yes             | Generate once via `php artisan key:generate --show` and store securely. |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Yes             | External MySQL connection; containers never start MySQL in prod.        |
| `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`                      | Yes (host/port) | Redis for cache + queues; passwords optional but recommended.           |
| `CACHE_STORE`, `QUEUE_CONNECTION`                                 | Yes             | Set to `redis` for consistency with worker containers.                  |
| `SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS`                      | Yes             | Match the UI domain (no protocol) for cookie binding.                   |
| `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`                      | Yes             | `true` + `lax` (or `none` if cross-site) to enforce HTTPS cookies.      |
| `FILESYSTEM_DISK`, `VITE_ASSET_BASE`                              | Yes             | Keep `public` + `/storage` unless migrating to S3.                      |
| `LOG_CHANNEL`, `LOG_LEVEL`                                        | Optional        | Defaults `stack` / `warning`; override for log aggregation.             |

Inject secrets via your orchestrator (Doppler, 1Password Connect, AWS SSM, etc.). Never commit
populated `.env` files.

### Deployment Checklist

1. **Prep secrets:** Copy `src/.env.production.example` into your secrets store and fill all
   required keys (DB, Redis, mail, Stripe, OAuth providers, etc.).
2. **Obtain images:** `docker compose -f deploy/prod/docker-compose.yml pull` (or build locally via
   Bake/Make).
3. **Provision storage:** `docker volume create petcare-storage` (or mount a host path to
   `/var/www/html/storage/app/public`).
4. **Start stack:** `docker compose -f deploy/prod/docker-compose.yml up -d`.
5. **Run migrations:**
   `docker compose -f deploy/prod/docker-compose.yml exec app php artisan migrate --force`.
6. **Warm caches (optional):**
   `docker compose -f deploy/prod/docker-compose.yml exec app php artisan config:cache route:cache view:cache`.
7. **Seed storage link (if needed):**
   `docker compose -f deploy/prod/docker-compose.yml exec app php artisan storage:link`.
8. **Health check:** `docker compose -f deploy/prod/docker-compose.yml ps` and
   `curl -f https://your-api.example.com/health`.
9. **UI smoke test:** Verify React assets load via both the `ui` and `pwa` containers and that
   `/storage/*` URLs resolve uploaded media.
10. **Log verification:** Inspect
    `docker compose -f deploy/prod/docker-compose.yml logs -f web app worker` for warnings before
    handing off.

### Troubleshooting

- **UI cannot call API (401/CORS):** Ensure `FRONTEND_URL`, `APP_URL`, `SESSION_DOMAIN`, and
  `SANCTUM_STATEFUL_DOMAINS` share the same base domain for both frontends; mismatched values break
  Sanctum cookies.
- **Storage URLs return 404:** Confirm the `storage` volume is mounted. When not relying on the
  Nginx alias, run `php artisan storage:link` inside the container.
- **Healthchecks stuck in `starting`:** External DB or Redis is unreachable. Validate firewalls, TLS
  requirements, and credentials; check `docker compose ... logs app web`.
- **Migrations fail with permission errors:** The PHP container runs as `www-data` (UID 82).
  Guarantee that mounted host paths grant read/write for that UID or adjust ownership via
  `chown -R 82:82`.
- **Browser reports CORS errors:** Review `config/cors.php` `allowed_origins` and ensure HTTPS
  termination happens before requests reach Nginx so headers match expectations.

### Backup & Restore

- **Database:** Schedule `mysqldump --single-transaction` (or managed snapshot) jobs. Encrypt and
  retain at least 7 daily copies plus weekly/monthly archives.
- **Uploads:** Nightly `tar` or `rsync` the storage path (e.g., `/srv/petcare/storage`) to object
  storage. Track checksums so restores can be validated quickly.
- **Configuration:** Store the production `.env`, Docker Compose overrides, and SSL certificates in
  a secure secrets vault with rotation reminders.
- **Restore drills:** Periodically rehearse restoring DB + storage into a staging namespace to
  validate the process before emergencies.

### Monitoring & Logging

- **Logs:** Tail via `docker compose -f deploy/prod/docker-compose.yml logs -f web app worker` or
  forward to a collector (Grafana Loki, Datadog, ELK). Laravel defaults to `stack` → `daily`.
- **Health endpoints:** `/health` (Nginx) and process-level checks for worker/scheduler/horizon are
  preconfigured; hook them into uptime monitors or Prometheus exporters.
- **Metrics:** Track Docker CPU/RAM, Redis memory/latency, MySQL slow queries, queue backlog, and
  failed jobs (`php artisan queue:failed`). Horizon provides additional queue metrics when enabled.

## Staging Environment

The staging environment mirrors the `develop` branch and keeps production isolated.

### Branch and release flow

- Feature work merges into `develop`.
- `develop` pushes trigger the `build-develop-images` workflow to publish staging images.
- Release Please manages production: Release PR → merge to `main` → `build-images` workflow
  publishes versioned images (no `latest`).
- Production release cadence and VERSION remain unchanged.

### Staging workflow behavior

- Workflow: `.github/workflows/build-develop-images.yml`
- Trigger: `push` to `develop` touching `src/**`, `docker/**`, `docker-bake.hcl`, or the workflow
  itself.
- Build: `docker buildx bake` using the `develop` group targeting `app`, `web`, and `ui`.
- Tags only: `:develop` and `:develop-${GITHUB_SHA}` for each image (no `latest`, no VERSION reads,
  no Release Please hook).
- Cache: GitHub Actions cache (`gha`) for faster rebuilds.

### Tag strategy

- Staging: `ghcr.io/slightlyprivate/petcare-companion-<service>:develop` and `:develop-${sha}`.
- Production: versioned tags from `VERSION` (`staging-{version}` / `release-{version}`), no
  `latest`.
- No staging tags cross over to production or release automation.

### Deploying staging

Files live in `deploy/staging/`.

1. Copy `.env.example` to `.env` and fill secrets (`APP_KEY`, DB credentials, mail sender, etc.).
2. From the staging host directory (e.g., `/srv/petcare-staging`):

   ```bash
   docker compose -f docker-compose.yml up -d
   ```

3. Services exposed on non-production ports:
   - Web: `9080`
   - UI: `9081`
   - MailHog UI/SMTP: `8026` / `1026`
   - MySQL and Redis bound to loopback (`3308`, `6380`) to avoid collisions.

### Keeping staging up to date

- Manual: run `./update.sh` to pull the newest develop images and recreate containers with minimal
  interruption.
- Cron example (every hour):

  ```cron
  0 * * * * cd /srv/petcare-staging && ./update.sh >> /var/log/petcare-staging.log 2>&1
  ```

- Webhook: point a GitHub repository dispatch/webhook at `/srv/petcare-staging/update.sh` to refresh
  after successful `develop` builds.

## API Infrastructure Exposure

### Overview

The PetCare Companion API is exposed via the **`web` container** (Nginx reverse proxy) which
forwards requests to the **`app` container** (PHP-FPM Laravel application). For production and
staging deployments using Traefik as the edge reverse proxy, the `web` container must be properly
configured with Traefik labels to enable external access.

### Container Architecture

```
Internet → Traefik → web (Nginx) → app (PHP-FPM Laravel)
```

- **Traefik**: Edge reverse proxy handling TLS termination, routing, and load balancing
- **web**: Nginx container serving as the Laravel API gateway (port 80 internally)
- **app**: PHP-FPM container running the Laravel application

### Domain Mapping

The API should be exposed on a subdomain following the pattern:

| Environment | Recommended Domain                                    | Example                                         |
| ----------- | ----------------------------------------------------- | ----------------------------------------------- |
| Development | `web.develop.petcare.<your-domain>`                   | `web.develop.petcare.slightlybetter.io`         |
| Staging     | `web.staging.petcare.<your-domain>`                   | `web.staging.petcare.slightlybetter.io`         |
| Production  | `api.petcare.<your-domain>` or `web.petcare.<domain>` | `api.petcare.slightlybetter.io` (user-facing)   |
| Blue/Green  | Same domain (both slots), only active slot enabled    | Both use `api.petcare.slightlybetter.io` router |

**Note:** For cleaner production URLs, consider using `api.petcare.*` instead of `web.petcare.*` in
the `WEB_DOMAIN` environment variable.

### Required Traefik Labels

Add the following labels to the `web` service in your deployment's `docker-compose.yml`:

```yaml
services:
  web:
    # ... other configuration ...
    networks:
      - default
      - traefik-proxy # Required: External network where Traefik discovers services
    labels:
      - 'traefik.enable=true'
      - 'traefik.http.routers.petcare_web_${ENV}.rule=Host(`${WEB_DOMAIN}`)'
      - 'traefik.http.routers.petcare_web_${ENV}.entrypoints=${TRAEFIK_ENTRYPOINT}'
      - 'traefik.http.routers.petcare_web_${ENV}.tls.certresolver=${TRAEFIK_CERT_RESOLVER}'
      - 'traefik.http.services.petcare_web_${ENV}.loadbalancer.server.port=80'

networks:
  traefik-proxy:
    external: true
```

### Environment Variables for Traefik

Set these in your deployment `.env` file:

```bash
# Domain Configuration
WEB_DOMAIN=api.petcare.slightlybetter.io

# Traefik Configuration
TRAEFIK_ENTRYPOINT=websecure        # Use 'web' for HTTP, 'websecure' for HTTPS
TRAEFIK_CERT_RESOLVER=cfresolver    # Your Traefik certificate resolver name
ENV=prod                             # Environment identifier (dev, staging, prod)
```

### Blue/Green Deployment Considerations

For production blue/green deployments, both slots share the same router name and domain:

**Slot-specific Configuration:**

```yaml
# production-blue/docker-compose.yml and production-green/docker-compose.yml
web:
  labels:
    - 'traefik.enable=${TRAEFIK_ENABLE}' # Toggled between true/false
    - 'traefik.http.routers.petcare_web.rule=Host(`${WEB_DOMAIN}`)' # No ${ENV} suffix
    - 'traefik.http.routers.petcare_web.entrypoints=${TRAEFIK_ENTRYPOINT}'
    - 'traefik.http.routers.petcare_web.tls.certresolver=${TRAEFIK_CERT_RESOLVER}'
    - 'traefik.http.services.petcare_web_${SLOT}.loadbalancer.server.port=80' # Slot-specific service
```

**Key Points:**

- Only ONE slot has `TRAEFIK_ENABLE=true` at any time
- Both slots use the SAME router name (`petcare_web` without environment suffix)
- Service name includes `${SLOT}` (blue/green) to differentiate backend targets
- Traefik automatically routes traffic to the enabled slot
- Deployment script toggles `TRAEFIK_ENABLE` between slots for zero-downtime switching

### Health Check Endpoints

The API exposes the following health check endpoints for monitoring:

- `GET /health` - Basic health check (returns 200 OK; unauthenticated)
- `GET /up` - Laravel's built-in health check (unauthenticated)
- `GET /api/health` - Recommended API health probe for load balancers/monitors (returns 200 OK;
  unauthenticated)
- `GET /api/auth/status` - Authenticated status (requires valid Bearer token). NOT a public health
  endpoint — do not use for Traefik/monitoring probes unless you can supply tokens.

Configure Traefik health checks:

```yaml
labels:
  - 'traefik.http.services.petcare_web_${ENV}.loadbalancer.healthcheck.path=/health'
  - 'traefik.http.services.petcare_web_${ENV}.loadbalancer.healthcheck.interval=10s'
  - 'traefik.http.services.petcare_web_${ENV}.loadbalancer.healthcheck.timeout=3s'
```

### CORS and Sanctum Configuration

Token-based API (Sanctum guard => [])

This project uses pure Bearer-token API authentication (Sanctum `'guard' => []`). Do not configure
cookie/session-based Sanctum settings — they are removed from the recommended configuration.

Backend .env (recommended)

```bash
APP_URL=https://api.petcare.slightlybetter.io
FRONTEND_URL=https://ui.petcare.slightlybetter.io,https://pwa.petcare.slightlybetter.io

# Not needed for token-based auth (kept for reference only):
# SANCTUM_STATEFUL_DOMAINS=
# SESSION_DOMAIN=
# SESSION_SECURE_COOKIE=
# SESSION_SAME_SITE=
```

- Use Bearer tokens for all API clients (Authorization: Bearer <token>).
- Keep FRONTEND_URL and CORS so browsers can contact the API.
- If you re-enable SPA cookie auth in the future, document it separately and reintroduce these
  settings only then.
- When Sanctum `guards` is empty (`[]`), treat stateful/session settings as legacy — do not enable
  them.

LEGACY — Stateful SPA mode (NOT used in current implementation)

```bash
# Only required when using Sanctum cookie-based SPA auth (web guard enabled)
SANCTUM_STATEFUL_DOMAINS=ui.petcare.slightlybetter.io,pwa.petcare.slightlybetter.io
SESSION_DOMAIN=.petcare.slightlybetter.io
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### Verification Steps

After deploying with Traefik labels:

1. **DNS Resolution**: Ensure `WEB_DOMAIN` resolves to your Traefik instance
   ```bash
   dig api.petcare.slightlybetter.io
   ```
2. **Traefik Discovery**: Check Traefik dashboard to confirm service is discovered
3. **TLS Certificate**: Verify certificate is issued and valid
   ```bash
   curl -I https://api.petcare.slightlybetter.io/health
   ```
4. **API Response**: Test an unauthenticated endpoint
   ```bash
   curl https://api.petcare.slightlybetter.io/api/public/pets
   ```
5. **Authentication**: Test authenticated endpoint with token
   ```bash
   curl -H "Authorization: Bearer YOUR_TOKEN" \
        https://api.petcare.slightlybetter.io/api/auth/me
   ```

### Troubleshooting

**503 Service Unavailable:**

- Check if `web` container is healthy: `docker ps`
- Verify `traefik-proxy` network exists and `web` is connected
- Confirm `traefik.enable=true` label is set
- Check Traefik logs for routing errors

**404 Not Found:**

- Verify `WEB_DOMAIN` matches the domain in your request
- Check router rule syntax in labels
- Ensure Traefik entrypoint is correct (`websecure` for HTTPS)

**Certificate Errors:**

- Verify `TRAEFIK_CERT_RESOLVER` matches your Traefik configuration
- Check Traefik logs for ACME challenge failures
- Ensure DNS records are correct for Let's Encrypt validation

**CORS Errors:**

- Verify `FRONTEND_URL` includes all frontend domains
- Ensure your backend CORS configuration allows requests from all relevant frontend origins
- Confirm that your API returns the correct CORS headers for token-based authentication

## Related Files

- `deploy/prod/docker-compose.yml` — Production services and volumes.
- `deploy/prod/update.sh` — Helper script for pulling new images with minimal downtime.
- `deploy/staging/docker-compose.yml` — Staging environment with Traefik labels configured.
- `deploy/production-blue/docker-compose.yml` — Blue slot with Traefik labels.
- `deploy/production-green/docker-compose.yml` — Green slot with Traefik labels.
- `docs/CI_CD_SETUP.md` — Build workflows and CI job descriptions.
