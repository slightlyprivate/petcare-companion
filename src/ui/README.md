# PetCare Companion — UI

- Stack: Vite, React, Tailwind (v4 via `@tailwindcss/vite`), TanStack Query
- Location: `src/ui`

Dev

- Prereq: Dev stack running via `docker compose -f docker-compose.dev.yml up`
- Start UI dev server: `npm install && npm run dev` inside `src/ui`
- API proxy: All `/api/*` calls are proxied to the BFF at `http://localhost:5174`

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
