# Staging Stack

This directory contains a staging deployment that tracks the `develop` branch builds published to
GHCR.

## Prerequisites

- Copy `.env.staging.example` to `.env.staging` and set secrets (at least `APP_KEY`, DB credentials,
  and hostnames).
- Ensure the host exposes open ports that do not clash with production:
  - Web: `9080`
  - UI: `9081`
  - MailHog: `8026` (UI) and `1026` (SMTP)
  - MySQL: `3308` (loopback)
  - Redis: `6380` (loopback)

## Usage

```bash
cd /srv/petcare-staging
cp .env.staging.example .env.staging   # first-time setup
docker compose -f docker-compose.yml up -d
```

Services use the `:develop` images:

- `ghcr.io/slightlyprivate/petcare-companion-app:develop`
- `ghcr.io/slightlyprivate/petcare-companion-web:develop`
- `ghcr.io/slightlyprivate/petcare-companion-ui:develop`

To refresh to the latest build, run `./update.sh` (safe to place behind cron or a webhook).
