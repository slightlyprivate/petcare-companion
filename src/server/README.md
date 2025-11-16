# PetCare Companion — Server

Lightweight Express server providing cookie-based sessions and a simple `/api/*` proxy to the Laravel backend.

Features

- Express app with `cookie-session`
- Health check at `/health`
- Session demo at `/session/ping`
- Generic `/api/*` proxy to `BACKEND_URL` (defaults to `http://localhost:8080`)
- Auth routes under `/auth`: `POST /auth/request`, `POST /auth/verify`, `POST /auth/logout`, `GET /auth/csrf`
- CSRF: `GET /auth/csrf` issues a token; all mutating `/api/*` require `X-CSRF-Token` header

Run

1) Copy env: `cp src/server/.env.example src/server/.env` and set real values
2) Start backend: `docker-compose up` (Laravel on `http://localhost:8080`)
3) Start server: `cd src/server && npm install && npm run dev`

Structure

- `src/server/src/constants.js`: shared constants (headers, cookie names, API prefix)
- `src/server/src/lib/config.js`: environment-driven config
- `src/server/src/lib/axios.js`: upstream API client (header forwarding, token injection)
- `src/server/src/lib/cookies.js`: cookie names and option builders
- `src/server/src/middleware/csrf.js`: CSRF token + mutation guard
- `src/server/src/routes/auth.js`: OTP request/verify/logout
- `src/server/src/services/proxy.js`: unified `/api/*` proxy handler
- `src/server/src/index.js`: app wiring, static, health, routers

Environment

- `SERVER_PORT` (default `5174`)
- `BACKEND_URL` (default `http://localhost:8080`)
- `SESSION_SECRET` (required; do not commit real secrets)
- `LARAVEL_API_KEY` (optional; sent as `X-Api-Key` to backend)
- `COOKIE_SECURE` (`true` in production)
- `COOKIE_SAMESITE` (`lax` default)
- `LOG_LEVEL` (`debug` | `info` | `warn` | `error`; default `info`)

Notes

- Proxy supports JSON and `application/x-www-form-urlencoded` bodies. For multipart uploads, add explicit handling.
- If using the UI at `src/ui`, you can point its API calls to this server by changing the Vite dev proxy target to `http://localhost:5174` instead of `http://localhost:8080`.
- Login flow: call `POST /auth/request` with `{ email }`, then `POST /auth/verify` with `{ email, code }`. A secure httpOnly cookie is set and used for API calls.
- Logging: Structured JSON logs with `LOG_LEVEL` control. Errors are returned as `{ error: { message, code? } }` via a centralized error handler.
