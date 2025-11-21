# PetCare Companion — Monorepo

A lightweight, educational monorepo demonstrating a Laravel API with a React UI, containerized with
Docker Compose.

## Overview

- Purpose: Showcase clean API design and a modern UI with Laravel Sanctum authentication.
- Audience: Developers exploring Laravel + Vite/React with Docker.
- Scope: Non-production, minimal footprint, no secrets committed.
- Core workflows: Shared caregiving access, daily routine scheduling, activity timeline logging
  (documented via Scribe; see `docs/demo-scenario.md` for a concise walkthrough).

## Services

- api: Laravel 12 application (path: `src/`), served behind Nginx.
- web: Nginx reverse proxy for the Laravel API (port 8080 → 80 in container).
- ui: Vite dev server with HMR for the React UI (development only).
- db: MySQL 8.0 with persistent volume.
- redis: Redis 7 for cache/queue experimentation.

## Repository Map

```mermaid
graph LR
  subgraph Client
    A[Browser]
  end

  subgraph Frontend
    F[frontend-ui<br/>Vite + React UI]
  end

  subgraph API
    W[web<br/>Nginx]
    P[app<br/>Laravel PHP-FPM]
  end

  D[(db<br/>MySQL 8.0)]
  R[(redis<br/>Redis 7)]

  A -->|HTTP :5173| F
  F -->|/api/* /sanctum/*| W
  W --> P
  P --> D
  P --> R
```

## Quick Start (Development)

- Copy env: `cp .env.example .env`
- Start dev stack: `docker compose up`
- Generate app key: `docker compose exec app php artisan key:generate`
- Migrate + seed:
  `docker compose exec app php artisan migrate && docker compose exec app php artisan db:seed`
- Shared storage (uploads) is mounted between `app` and `web` and served from `/storage`

## Ports

- API: `http://localhost:8080`
- UI (Vite dev server): `http://localhost:5173`
- MySQL: `localhost:3307`
- Redis: `localhost:6379`

## Dev Notes

- Laravel commands: `docker-compose exec app php artisan <cmd>`

### Queue/Cache with Redis (Dev)

- `.env` now defaults to Redis: `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis` with
  `REDIS_HOST=redis`.
- PHP image includes `phpredis` extension (installed via PECL in `docker/app.Dockerfile`).
- Workers still run as a separate service, but you can use Horizon for dashboarding.

### Horizon (Optional)

- Compose includes a `horizon` service which can run `php artisan horizon`.
- Rebuild PHP images to enable required extensions (`pcntl`, `posix`, `redis`):
  - `docker compose build app worker scheduler horizon`
- Enable Horizon runtime:
  - Edit `docker-compose.yml` and set `ENABLE_HORIZON: "true"` under the `horizon` service.
- Install Horizon before enabling it:
  - `docker compose exec app bash -lc "composer require laravel/horizon:^5.23 && php artisan horizon:install && php artisan migrate"`
- Access dashboard at `/horizon` (served via the `web` service).
- If you see "Command \"horizon\" is not defined", ensure you've run the composer and artisan steps
  above, or keep `ENABLE_HORIZON` set to `false` until installation is complete.
- In production, run Horizon as its own process and secure the dashboard behind auth or IP
  allowlists.

### Code Formatting (Prettier)

- Config lives at the repo root: `prettier.config.cjs` and `.prettierignore`.
- UI:
  - Install deps (first run): `cd src/ui && npm install`
  - Format: `npm run format`
  - Check: `npm run format:check`
- In containers:
  - UI (frontend-ui): `docker compose exec ui sh -lc "npm run format"`

### Pre-commit Hook (Husky)

- Install root dev deps once: `npm install` (at repo root). This runs `husky install` via `prepare`.
- Husky hook: `.husky/pre-commit` uses `lint-staged` to run Prettier only on staged files.
- Manual formatting across both projects: `npm run format` (root) or check `npm run format:check`.

### Development Stack

- Use `docker-compose.yml` (now dev-focused) for development. It includes: Laravel (app), Nginx
  (web), MySQL (db), Redis (redis), Queue worker, Scheduler, Horizon (optional), and Vite UI with
  HMR.
- UI (Vite HMR): <http://localhost:5173>
- API (Nginx → PHP-FPM): <http://localhost:8080>
- Production usage: use ONLY the production file: `docker compose -f docker-compose.prod.yml up -d`
  (Do not combine with the dev compose; that would start local MySQL/Redis and bind mounts
  unintended for production.)

## Production Run Steps

Build & push images (CI or local):

```bash
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml push
```

Or pull prebuilt images:

```bash
docker compose -f docker-compose.prod.yml pull
```

Prepare environment file (`.env`) with external service hosts:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-api.example.com
FRONTEND_URL=https://your-ui.example.com
DB_CONNECTION=mysql
DB_HOST=your-managed-mysql-host
DB_PORT=3306
DB_DATABASE=petcare
DB_USERNAME=petcare
DB_PASSWORD=****
REDIS_HOST=your-managed-redis-host
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=your-ui.example.com
SESSION_DOMAIN=your-ui.example.com
VITE_API_BASE=/api
VITE_ASSET_BASE=/storage
```

Create or attach persistent storage volume (if using Docker volume):

```bash
docker volume create storage
```

Alternatively change `docker-compose.prod.yml` to a host bind mount:

```yaml
volumes:
  - /srv/petcare/storage:/var/www/html/storage/app/public:rw
```

Start stack:

```bash
docker compose -f docker-compose.prod.yml up -d
```

Run migrations (one-off):

```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

Optional cache warmups:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
```

Health validation:

- Nginx: `curl -f https://your-api.example.com/health`
- UI static assets served correctly.

### Important: Avoid Combining Dev & Prod Files

Combining `docker-compose.yml` (dev) with `docker-compose.prod.yml` would unintentionally start
development-only services (MySQL, Redis, Horizon, bind mounts) in production. Keep production
concerns isolated to the prod file to ensure:

- Correct dependency on external managed DB/Redis
- Immutable containers (no source bind mounts)
- Reduced attack surface (no dev tooling)
- Predictable lifecycle and scaling

## Uploads & Storage

- Laravel uses the `public` disk backed by the shared `storage` volume mounted on `app` and `web`.
- Nginx serves uploaded assets from `/storage/*` (see `docker/nginx.conf`); no proxy layer or BFF is
  required.
- Upload API: `POST /api/uploads` (auth) accepts images/MP4/WebM up to 10MB and returns the stored
  `path` and public `url`.
- React uses `VITE_ASSET_BASE` (default `/storage`) to resolve media URLs from activity uploads.
- Default directories: activities → `activities/media`, pet avatars → `pets/avatars`, general →
  `uploads`.

## Auth & Cookies

- Flow: The React UI authenticates with Laravel using OTP login. Laravel Sanctum manages
  session-based authentication using cookies. The UI makes direct API calls to Laravel endpoints.
- CSRF: Laravel Sanctum provides CSRF protection via `/sanctum/csrf-cookie`. The UI fetches this
  endpoint to get the XSRF-TOKEN cookie, which is then sent as the `X-XSRF-TOKEN` header on mutating
  requests.
- Logout: `POST /api/auth/logout` clears the session/cookies and revokes the current Sanctum token
  in Laravel.
- Cookies: In production, ensure `SESSION_SECURE_COOKIE=true` in Laravel's `.env` and configure CORS
  appropriately for cross-origin requests.

  **Laravel production config references:**
  - **CORS:** Edit `config/cors.php` to allow your UI domain:

    ```php
    // config/cors.php
    return [
        'paths' => ['api/*', 'sanctum/csrf-cookie'],
        'allowed_origins' => ['https://your-ui-domain.com'],
        'supports_credentials' => true,
    ];
    ```

  - **Sanctum domains:** Edit `SANCTUM_STATEFUL_DOMAINS` in `.env` to include your UI domain:

    ```env
    SANCTUM_STATEFUL_DOMAINS=your-ui-domain.com
    ```

  - **Session settings:** In `.env`, ensure cookies are secure and same-site is set for
    cross-origin:

    ```env
    SESSION_SECURE_COOKIE=true
    SESSION_SAME_SITE=lax
    ```

  - See also: `config/session.php` for session driver and cookie settings.

## Production Compose (Reference)

- `docker-compose.prod.yml` is production-oriented and references prebuilt images (no bind mounts).
- DB/Redis are expected to be external; set connection variables in `.env`.
- Build & push images before deploying (`ghcr.io/yourorg/*`).
- UI should be served as built static assets behind CDN / Nginx.
- Configure CORS & Sanctum domains appropriately in `.env`.

Environment wiring (UI)

- UI build-time vars (Vite):
  - `VITE_API_BASE` (default `/api`): required in production builds. Set to your Laravel API base
    URL.
  - `VITE_API_PROXY_TARGET`: dev-only, points Vite's dev proxy at the Laravel backend (e.g.,
    `http://web`).
  - `VITE_ASSET_BASE` (default `/storage`): base path/URL for uploaded files returned by Laravel.

## Documentation

- API (Laravel): `src/README.md`
- UI (Vite + React): `src/ui/README.md`
- Architecture: `docs/architecture.md`
- Postman: `src/storage/app/scribe/collection.json`

## CI

- UI image build/push: `.github/workflows/ui.yml` (pushes to GHCR)
