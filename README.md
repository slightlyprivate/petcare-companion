# PetCare Companion — Monorepo

A lightweight, educational monorepo demonstrating a Laravel API, a React UI, and a Node-based BFF
(Backend for Frontend), containerized with Docker Compose.

## Overview

- Purpose: Showcase clean API design, a simple BFF layer, and a modern UI.
- Audience: Developers exploring Laravel + Vite/React with Docker.
- Scope: Non-production, minimal footprint, no secrets committed.

## Services

- api: Laravel 12 application (path: `src/`), served behind Nginx.
- web: Nginx reverse proxy for the Laravel API (port 8080 → 80 in container).
- frontend: Node Express BFF serving the built React UI and proxying `/api/*` to `web`.
- db: MySQL 8.0 with persistent volume.
- redis: Redis 7 for cache/queue experimentation.

## Repository Map

```mermaid
graph LR
  subgraph Client
    A[Browser]
  end

  subgraph Frontend
    F[frontend<br/>Node BFF + React UI]
  end

  subgraph API
    W[web<br/>Nginx]
    P[app<br/>Laravel PHP-FPM]
  end

  D[(db<br/>MySQL 8.0)]
  R[(redis<br/>Redis 7)]

  A -->|HTTP :5174| F
  F -->|/api/* proxy| W
  W --> P
  P --> D
  P --> R
```

## Quick Start (Development)

- Copy env: `cp .env.example .env`
- Start dev stack: `docker compose -f docker-compose.dev.yml up`
- Generate app key: `docker compose -f docker-compose.dev.yml exec app php artisan key:generate`
- Migrate + seed:
  `docker compose -f docker-compose.dev.yml exec app php artisan migrate && docker compose -f docker-compose.dev.yml exec app php artisan db:seed`

## Ports

- API: `http://localhost:8080`
- Frontend: `http://localhost:5174`
- MySQL: `localhost:3307`
- Redis: `localhost:6379`

## Dev Notes

- Laravel commands: `docker-compose exec app php artisan <cmd>`
- BFF env: `SERVER_PORT`, `BACKEND_URL`, `SESSION_SECRET`, `COOKIE_SECURE`, `COOKIE_SAMESITE`

### Queue/Cache with Redis (Dev)

- `.env` now defaults to Redis: `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis` with
  `REDIS_HOST=redis`.
- PHP image includes `phpredis` extension (installed via PECL in `docker/app.Dockerfile`).
- Workers still run as a separate service, but you can use Horizon for dashboarding.

### Horizon (Optional)

- Compose includes a `horizon` service which can run `php artisan horizon`.
- Rebuild PHP images to enable required extensions (`pcntl`, `posix`, `redis`):
  - `docker compose -f docker-compose.dev.yml build app worker scheduler horizon`
- Enable Horizon runtime:
  - Edit `docker-compose.dev.yml` and set `ENABLE_HORIZON: "true"` under the `horizon` service.
- Install Horizon before enabling it:
  - `docker compose -f docker-compose.dev.yml exec app bash -lc "composer require laravel/horizon:^5.23 && php artisan horizon:install && php artisan migrate"`
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
- BFF Server:
  - Install deps (first run): `cd src/server && npm install`
  - Format: `npm run format`
  - Check: `npm run format:check`
- In containers:
  - UI (frontend-ui):
    `docker compose -f docker-compose.dev.yml exec frontend-ui sh -lc "npm run format"`
  - BFF (frontend):
    `docker compose -f docker-compose.dev.yml exec frontend sh -lc "cd src/server && npm run format"`

### Pre-commit Hook (Husky)

- Install root dev deps once: `npm install` (at repo root). This runs `husky install` via `prepare`.
- Husky hook: `.husky/pre-commit` uses `lint-staged` to run Prettier only on staged files.
- Manual formatting across both projects: `npm run format` (root) or check `npm run format:check`.

### Dev Compose (Single Stack)

- Use `docker-compose.dev.yml` for development. It includes: Laravel (app), Nginx (web), MySQL (db),
  Redis (redis), Queue worker, Scheduler, BFF (frontend), and Vite UI (frontend-ui) with live
  reload.
- BFF (Express): <http://localhost:5174>
- UI (Vite HMR): <http://localhost:5173>
- API (Nginx → PHP-FPM): <http://localhost:8080>

## Auth & Cookies

- Flow: The BFF completes OTP login with Laravel, stores the returned Sanctum token in a server-side
  session, and injects `Authorization: Bearer <token>` on proxied API requests. The browser never
  sees the token.
- CSRF: The BFF issues a CSRF token and requires it for mutating `/api/*` requests. Laravel API
  routes use stateless token auth; no double-CSRF required.
- Logout: `POST /auth/logout` clears the session/cookies and revokes the current Sanctum token in
  Laravel.
- Cookies: In production set `COOKIE_SECURE=true` and choose `COOKIE_SAMESITE=lax` (or `strict`) in
  `src/server/.env`.

## Production Compose (Reference)

- `docker-compose.yml` is production-oriented and references prebuilt images (no bind mounts).
  DB/Redis are expected to be external; set connection variables in `.env`.
- Not used during development; build/publish images before using it.

- Expose only the `frontend` service (Node BFF + static UI). Laravel (`web` + `app`) remains
  internal. The BFF proxies `/api/*` to Laravel via the Docker network.

## Documentation

- API (Laravel): `src/README.md`
- BFF Server: `src/server/README.md`
- UI (Vite + React): `src/ui/README.md`
- Architecture: `docs/architecture.md`
- Postman: `src/storage/app/private/scribe/collection.json`

## CI

- UI image build/push: `.github/workflows/ui.yml` (pushes to GHCR)
