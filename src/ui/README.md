# PetCare Companion — UI

- Stack: Vite, React, Tailwind (v4 via `@tailwindcss/vite`), TanStack Query
- Location: `src/ui`

Dev

- Prereq: Dev stack running via `docker compose -f docker-compose.dev.yml up`
- Start UI dev server: `npm install && npm run dev` inside `src/ui`
- API proxy: The dev server proxies application calls to the BFF at `http://localhost:5174` (or
  `VITE_API_PROXY_TARGET`):
  - `/api/*` → BFF (Laravel upstream)
  - `/auth/*`, `/user/*`, `/pets/*`, `/appointments/*`, `/credits/*`, `/gifts/*` → BFF endpoints

Proxy target

- In containerized dev (docker compose), Vite reads `VITE_API_PROXY_TARGET` from env and proxies to
  `http://frontend:3000` (the BFF service name).
- If running Vite locally on your host, set `VITE_API_PROXY_TARGET=http://localhost:5174` before
  `npm run dev`, or create `.env.local` with that variable.

Scripts

- `npm run dev`: Start Vite dev server on `http://localhost:5173`
- `npm run build`: Production build to `dist/`
- `npm run preview`: Preview build (serves `dist/`)

Notes

- Tailwind v4 is enabled through the Vite plugin; no extra config is required.
- Example screen queries `/api/public/pets` to demonstrate TanStack Query usage.

HTTP clients

- `http.api`: Use for calls to the upstream Laravel API (prefix `VITE_API_BASE`, default `/api`).
- `http.proxy`: Use for calls to the local BFF/Proxy (prefix `VITE_PROXY_BASE`). Prefer this for
  auth flows and CSRF.
- `request`: Low-level helper when you need a custom base or atypical options. Default includes
  credentials and Axios-based retries.

Environment variables

- `VITE_API_BASE`: Base URL for Laravel API (default `/api`). Required in production.
- `VITE_PROXY_BASE`: Base URL for BFF (can be empty in dev to use relative paths via Vite proxy;
  REQUIRED in production).
- `VITE_API_PROXY_TARGET`: Dev-only proxy target for Vite (e.g., `http://frontend:3000` from
  docker-compose.dev.yml, or `http://localhost:5174`).
- See `.env.example` in `src/ui` for typical values.

CSRF endpoint

- The UI fetches CSRF via `GET /auth/csrf` (BFF). The BFF exposes this route and returns
  `{ csrfToken, ttlMs?, expiresAt? }`.
- Ensure the BFF container is reachable (via `VITE_API_PROXY_TARGET` in dev or `VITE_PROXY_BASE` in
  production) before building user flows.

API barrels

- Import domain clients and hooks via `src/ui/src/api/index.ts` to simplify imports as features
  grow: `import { pets, petHooks } from '@/api'`.

Query helpers

- Prefer `useAppQuery` / `useAppMutation` from `src/ui/src/lib/appQuery` for consistent defaults.
  They layer on top of the global QueryClient defaults and keep per-domain options tidy.
- For cursor/page flows, use `usePaginatedQuery` to keep previous data while fetching the next page.
  Example:
  `usePaginatedQuery({ queryKey: [qk.pets.all, page], queryFn: () => listPublicPets({ page }), keepPreviousData: true })`.

Optimistic updates

- `src/ui/src/lib/optimistic.ts` provides `optimisticListUpdate` to stage UI updates before the
  server responds (e.g., prepend a newly created item). Spread the returned handlers into your
  `useAppMutation` call; it snapshots cache, rolls back on error, and invalidates on settle.

Layout ownership

- `AppLayout` (in `layouts/`) owns the navigation shell and renders routed content via `<Outlet />`.
  There is no separate `AppShell`—the layout is consolidated to reduce confusion and duplication.

CSRF visibility and debugging

- CSRF is fetched via `/auth/csrf` and persisted in localStorage where available. In dev, the app
  logs warnings when storage is unavailable (SSR/private modes) or when a CSRF refresh fails after
  a 419.
