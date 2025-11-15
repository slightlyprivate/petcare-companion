# PetCare Companion — Server

Lightweight Express server providing cookie-based sessions and a simple `/api/*` proxy to the Laravel backend.

Features

- Express app with `cookie-session`
- Health check at `/health`
- Session demo at `/session/ping`
- Generic `/api/*` proxy to `BACKEND_URL` (defaults to `http://localhost:8080`)

Run

1) Copy env: `cp src/server/.env.example src/server/.env` and set real values
2) Start backend: `docker-compose up` (Laravel on `http://localhost:8080`)
3) Start server: `cd src/server && npm install && npm run dev`

Environment

- `SERVER_PORT` (default `5174`)
- `BACKEND_URL` (default `http://localhost:8080`)
- `SESSION_SECRET` (required; do not commit real secrets)

Notes

- Proxy supports JSON and `application/x-www-form-urlencoded` bodies. For multipart uploads, add explicit handling.
- If using the UI at `src/ui`, you can point its API calls to this server by changing the Vite dev proxy target to `http://localhost:5174` instead of `http://localhost:8080`.
