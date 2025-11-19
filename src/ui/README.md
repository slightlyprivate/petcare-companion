# PetCare Companion — UI

- Stack: Vite, React, Tailwind (v4 via `@tailwindcss/vite`), TanStack Query
- Location: `src/ui`

Dev

- Prereq: Dev stack running via `docker compose -f docker-compose.dev.yml up`
- Start UI dev server: `npm install && npm run dev` inside `src/ui`
- API proxy: The dev server proxies application calls to Laravel at `http://localhost:8000` (or
  `VITE_API_PROXY_TARGET`):
  - `/api/*` → Laravel API
  - `/sanctum/*` → Laravel Sanctum (CSRF, auth)
  - `/storage/*` → Laravel storage (uploaded files)

Proxy target

- In containerized dev (docker compose), Vite reads `VITE_API_PROXY_TARGET` from env and proxies to
  `http://web` (the Laravel service name).
- If running Vite locally on your host, set `VITE_API_PROXY_TARGET=http://localhost:8000` before
  `npm run dev`, or create `.env.local` with that variable.

Scripts

- `npm run dev`: Start Vite dev server on `http://localhost:5173`
- `npm run build`: Production build to `dist/`
- `npm run preview`: Preview build (serves `dist/`)

Notes

- Tailwind v4 is enabled through the Vite plugin; no extra config is required.
- Authentication uses Laravel Sanctum with cookie-based sessions.
- CSRF protection is handled automatically via `X-XSRF-TOKEN` header.

Project structure guidance

- `lib/`: Cross-cutting utilities used by more than one domain (e.g., HTTP clients, CSRF helpers,
  query wrappers, notifications, config, brand utilities). These should be UI-agnostic and reusable.
- `api/<domain>`: Domain-specific clients (`client.ts`) and hooks (`hooks.ts`). Keep logic close to
  the domain; avoid importing UI components here.
- `components/`: Pure presentational building blocks (Button, Spinner, ErrorMessage, StatusMessage,
  QueryBoundary) that do not depend on domain code.
- `layouts/`: Shells and navigational structure; `AppLayout` owns the browser chrome.
- `pages/`: Route-level screens that compose components and domain hooks.
- `routes/`: Route config and bootstrapping; avoid feature logic here.

Routes

- Centralized route paths live in `src/ui/src/routes/paths.ts` as `PATHS` constants.
- Always import and use `PATHS` instead of hardcoding strings like `/dashboard` or `/auth/signin`.
- Route map (UI):
  - Public: `/` (Landing), `/discover`, `/pet/:slug`
  - Auth: `/auth/signin`, `/auth/signup`, `/auth/verify`
  - Dashboard: `/dashboard` (index → My Pets), `/dashboard/pets`, `/dashboard/pets/new`,
    `/dashboard/pets/:id`, `/dashboard/pets/:id/settings`, `/dashboard/appointments`,
    `/dashboard/gifts`, `/dashboard/account`, `/dashboard/admin/gift-types`
  - Not Found: `*`

Linting and route guard

- A lightweight guard prevents introducing hardcoded UI route strings:
  - `npm run lint:routes` checks for `to="/..."`, `path: '/...'`, and
    `window.location.assign('/...')`.
  - Add/modify routes by updating `paths.ts` and `routes.config.tsx` using those constants.
- ESLint config (`.eslintrc.cjs`) includes `no-restricted-syntax` selectors that will flag common
  patterns when ESLint is installed. Run `npm run lint` after adding ESLint to your dev deps.

HTTP clients

- `http.api`: Use for all calls to the Laravel API (prefix `VITE_API_BASE`, default `/api`).
- `request`: Low-level helper when you need a custom base or atypical options. Includes credentials
  and automatic CSRF token handling via `X-XSRF-TOKEN` header.

Environment variables

- `VITE_API_BASE`: Base URL for Laravel API (default `/api`). In production, set to your Laravel
  backend URL.
- `VITE_API_PROXY_TARGET`: Dev-only proxy target for Vite (e.g., `http://web` from
  docker-compose.dev.yml, or `http://localhost:8000` for local Laravel).
- See `.env.example` in `src/ui` for typical values.

CSRF protection

- The UI uses Laravel Sanctum's standard CSRF flow via `GET /sanctum/csrf-cookie`, which sets the
  `XSRF-TOKEN` cookie.
- The `X-XSRF-TOKEN` header is automatically attached to all unsafe requests (POST, PUT, PATCH,
  DELETE) by the HTTP client.
- CSRF tokens are cached and automatically refreshed on 419 responses.

API barrels

- Import domain clients and hooks via `src/ui/src/api/index.ts` to simplify imports as features
  grow: `import { pets, petHooks } from '@/api'`.

Query helpers

- Prefer `useAppQuery` / `useAppMutation` from `src/ui/src/lib/appQuery` for consistent defaults.
  They layer on top of the global QueryClient defaults and keep per-domain options tidy.
- For cursor/page flows, use `usePaginatedQuery` to keep previous data while fetching the next page.
  Example:
  `usePaginatedQuery({ queryKey: [qk.pets.all, page], queryFn: () => listPublicPets({ page }), keepPreviousData: true })`.

Cache invalidation patterns

- `src/ui/src/lib/queryUtils.ts` exposes helpers to reduce manual cache touches:
  - `invalidateMany(qc, [qk.pets.all, qk.pets.mine])`
  - `invalidateListAndDetail(qc, qk.pets.mine, qk.pets.detail(id))` Use these in mutation
    `onSuccess` blocks instead of reaching into cache data structures.

Shared UI primitives

- Favor primitives under `src/ui/src/components/` to keep Tailwind consistent:
  - `Button`, `StatusMessage`, `ErrorMessage`, `Spinner`, `QueryBoundary`
  - `Section`: standard titled card with border/padding
  - `TextInput`: consistent input styling for forms These reduce duplicated classes and visual drift
    between sections.

Linting and type safety

- TS is `strict: true` and we include a lightweight guard to prevent `any` usage:
  - `npm run lint:any` scans for `: any`, `as any`, `any[]`, `useState<any>`
- A Tailwind duplicate-class guard is available:
  - `npm run lint:twdups` flags repeated class tokens within a single `className` string.
- Combine all guards with `npm run lint:all`.

Testing plan (lightweight)

- CSRF behavior: Manually validate `GET /sanctum/csrf-cookie` sets the XSRF-TOKEN cookie. The UI
  logs dev warnings if a 419 refresh fails.
- For unit tests (future), prefer Vitest; focus on `ensureCsrf` happy/failure paths and HTTP client
  transient retry behavior (network/5xx retry, 419 refresh once). Avoid adding a test runner until
  the repository standardizes on one.

Optimistic updates

- `src/ui/src/lib/optimistic.ts` provides `optimisticListUpdate` to stage UI updates before the
  server responds (e.g., prepend a newly created item). Spread the returned handlers into your
  `useAppMutation` call; it snapshots cache, rolls back on error, and invalidates on settle.

Layout ownership

- `AppLayout` (in `layouts/`) owns the navigation shell and renders routed content via `<Outlet />`.
  There is no separate `AppShell`—the layout is consolidated to reduce confusion and duplication.

CSRF visibility and debugging

- CSRF tokens are obtained from the `XSRF-TOKEN` cookie set by `/sanctum/csrf-cookie`. A copy is
  cached in localStorage for faster access. In dev, the app logs warnings when a CSRF refresh fails
  after a 419.
