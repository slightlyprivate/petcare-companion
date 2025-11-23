# Production Deployment Guide

This guide captures everything required to run the PetCare Companion stack in production. It assumes
you are deploying the containers defined in `deploy/prod/docker-compose.yml` and are pulling images
from GHCR (`ghcr.io/slightlyprivate/petcare-companion-*`).

## Prerequisites

- Access to the production secrets manager (APP_KEY, DB credentials, API keys, Stripe, mail, etc.).
- Managed MySQL and Redis instances reachable from the Docker host.
- Docker Compose v2 on the target host plus permissions to create Docker volumes.
- HTTPS termination in front of the `web`/`ui` containers (reverse proxy, load balancer, or cloud
  ingress) so Sanctum cookies remain secure.

## Environment Variable Requirements

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

## Deployment Checklist

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

## Troubleshooting

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

## Backup & Restore

- **Database:** Schedule `mysqldump --single-transaction` (or managed snapshot) jobs. Encrypt and
  retain at least 7 daily copies plus weekly/monthly archives.
- **Uploads:** Nightly `tar` or `rsync` the storage path (e.g., `/srv/petcare/storage`) to object
  storage. Track checksums so restores can be validated quickly.
- **Configuration:** Store the production `.env`, Docker Compose overrides, and SSL certificates in
  a secure secrets vault with rotation reminders.
- **Restore drills:** Periodically rehearse restoring DB + storage into a staging namespace to
  validate the process before emergencies.

## Monitoring & Logging

- **Logs:** Tail via `docker compose -f deploy/prod/docker-compose.yml logs -f web app worker` or
  forward to a collector (Grafana Loki, Datadog, ELK). Laravel defaults to `stack` → `daily`.
- **Health endpoints:** `/health` (Nginx) and process-level checks for worker/scheduler/horizon are
  preconfigured; hook them into uptime monitors or Prometheus exporters.
- **Metrics:** Track Docker CPU/RAM, Redis memory/latency, MySQL slow queries, queue backlog, and
  failed jobs (`php artisan queue:failed`). Horizon provides additional queue metrics when enabled.

## Related Files

- `deploy/prod/docker-compose.yml` — Production services and volumes.
- `deploy/prod/update.sh` — Helper script for pulling new images with minimal downtime.
- `docs/CI_CD_SETUP.md` — Build workflows and CI job descriptions.
- `docs/staging-environment.md` — Reference for staging flow before promoting to production.
