# Staging Environment

The staging environment mirrors the `develop` branch and keeps production isolated.

## Branch and release flow

- Feature work merges into `develop`.
- `develop` pushes trigger the `build-develop-images` workflow to publish staging images.
- Release Please manages production: Release PR → merge to `main` → `build-images` workflow
  publishes versioned/`latest` images.
- Production release cadence and VERSION remain unchanged.

## Staging workflow behavior

- Workflow: `.github/workflows/build-develop-images.yml`
- Trigger: `push` to `develop` touching `src/**`, `docker/**`, `docker-bake.hcl`, or the workflow
  itself.
- Build: `docker buildx bake` using the `develop` group targeting `app`, `web`, and `ui`.
- Tags only: `:develop` and `:develop-${GITHUB_SHA}` for each image (no `latest`, no VERSION reads,
  no Release Please hook).
- Cache: GitHub Actions cache (`gha`) for faster rebuilds.

## Tag strategy

- Staging: `ghcr.io/slightlyprivate/petcare-companion-<service>:develop` and `:develop-${sha}`.
- Production: unchanged, versioned tags from `VERSION` plus `latest` from `main`.
- No staging tags cross over to production or release automation.

## Deploying staging

Files live in `deploy/staging/`.

1. Copy `.env.staging.example` to `.env.staging` and fill secrets (`APP_KEY`, DB credentials, mail
   sender, etc.).
2. From the staging host directory (e.g., `/srv/petcare-staging`):

   ```bash
   docker compose -f docker-compose.yml up -d
   ```

3. Services exposed on non-production ports:
   - Web: `9080`
   - UI: `9081`
   - MailHog UI/SMTP: `8026` / `1026`
   - MySQL and Redis bound to loopback (`3308`, `6380`) to avoid collisions.

## Keeping staging up to date

- Manual: run `./update.sh` to pull the latest develop images and recreate containers with minimal
  interruption.
- Cron example (every hour):

  ```cron
  0 * * * * cd /srv/petcare-staging && ./update.sh >> /var/log/petcare-staging.log 2>&1
  ```

- Webhook: point a GitHub repository dispatch/webhook at `/srv/petcare-staging/update.sh` to refresh
  after successful `develop` builds.
