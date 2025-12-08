# 🚀 Deployment Strategy & Image Tagging

> **⚠️ IMPORTANT: This is an example guide only!**
>
> This document provides **educational examples** of deployment strategies. The actual deployments
> for PetCare Companion are managed in a separate repository:
> [`homelab-slightly-server`](https://github.com/slightlyprivate/homelab-slightly-server). Paths and
> commands shown are illustrative; adapt them to your environment.

This document describes the CI/CD pipeline flow and image tagging strategy for PetCare Companion.

## 📋 Overview

```
develop branch → development environment (dev-{shortsha})
main branch    → staging environment (staging-{version})
               → production environment (release-{version})
```

## 🔄 CI/CD Pipeline Flow

### 1️⃣ Development Deployment

**Trigger:** Push to `develop` branch

**Build:**

- Build images with tag: `dev-{7-char-sha}` (e.g., `dev-4f31a8c`)
- Push to `${DOCKER_REGISTRY}/petcare-companion-*:dev-{shortsha}`
- Requires `DOCKER_REGISTRY` env var (e.g., `ghcr.io/slightlyprivate`)

**Deploy:**

- Target: `<your-deploy-path>/deploy/development/`
- Update `.env` with `IMAGE_TAG=dev-{shortsha}` and `DOCKER_REGISTRY=<your-registry>`
- Run: `docker compose up -d --pull always`

**Result:**

- Latest develop code deployed immediately
- Each commit gets unique, traceable image tag
- Fast iteration for feature development

---

### 2️⃣ Staging Deployment

**Trigger:** Push to `main` branch (or manual promotion)

**Build (build once, tag twice):**

```bash
# Set required registry
export DOCKER_REGISTRY=ghcr.io/slightlyprivate

# Build and push with staging tag
docker build -t ${DOCKER_REGISTRY}/petcare-companion-app:staging-1.2.3 .
docker push ${DOCKER_REGISTRY}/petcare-companion-app:staging-1.2.3

# Tag same image for production (no rebuild)
docker tag ${DOCKER_REGISTRY}/petcare-companion-app:staging-1.2.3 \
           ${DOCKER_REGISTRY}/petcare-companion-app:release-1.2.3
docker push ${DOCKER_REGISTRY}/petcare-companion-app:release-1.2.3
```

**Deploy to Staging:**

- Target: `<your-deploy-path>/deploy/staging/`
- Update `.env` with `IMAGE_TAG=staging` (moving alias) or `staging-1.2.3` and
  `DOCKER_REGISTRY=<your-registry>`
- Run: `docker compose up -d --pull always`

**Benefit:**

- **Same image for staging and production**
- What you test in staging is exactly what goes to production
- No "works in staging but fails in production" surprises

---

### 3️⃣ Production Deployment (Blue/Green)

**Trigger:** Manual approval after staging validation

**Blue/Green Strategy:**

1. **Read active slot:** `cat deploy/production/active-slot` → `blue`
2. **Deploy to inactive slot:** Deploy to `green`
3. **Health check:** Verify new slot is healthy
4. **Traefik swap:** Enable Traefik on new slot, disable on old slot
5. **Update tracker:** Write `green` to `active-slot` file

**Pipeline Logic:**

```bash
#!/bin/bash
# Example deployment script

# Require registry
if [ -z "$DOCKER_REGISTRY" ]; then
    echo "Error: DOCKER_REGISTRY must be set"
    exit 1
fi

# 1. Read current active slot
ACTIVE_SLOT=$(cat /srv/stacks/petcare-companion/deploy/production/active-slot)
echo "Current active slot: $ACTIVE_SLOT"

# 2. Determine target slot (opposite of active)
if [ "$ACTIVE_SLOT" = "blue" ]; then
    TARGET_SLOT="green"
else
    TARGET_SLOT="blue"
fi
echo "Deploying to inactive slot: $TARGET_SLOT"

# 3. Deploy to target slot
cd "/srv/stacks/petcare-companion/deploy/production-$TARGET_SLOT"
export IMAGE_TAG="release-1.2.3"
export DOCKER_REGISTRY
export TRAEFIK_ENABLE="false"  # Start inactive
# Watchtower is disabled on production slots by label; promotion is always manual.

docker compose up -d --pull always

# 4. Health check (wait for services to be healthy)
echo "Running health checks..."
sleep 30
# Ensure all services are healthy
total_services=$(docker compose ps --format json | jq '. | length')
healthy_services=$(docker compose ps --format json | jq '[.[] | select(.Health=="healthy")] | length')
if [ "$total_services" -eq 0 ] || [ "$healthy_services" -ne "$total_services" ]; then
    echo "ERROR: Not all services are healthy!"
    docker compose ps
    exit 1
fi
# 5. Activate new slot and deactivate old slot
echo "Activating $TARGET_SLOT slot..."
export TRAEFIK_ENABLE="true"
docker compose up -d  # Apply new Traefik labels

echo "Deactivating $ACTIVE_SLOT slot..."
cd "/srv/stacks/petcare-companion/deploy/production-$ACTIVE_SLOT"
export TRAEFIK_ENABLE="false"
docker compose up -d  # Remove from Traefik

# 6. Update active-slot tracker
echo "$TARGET_SLOT" > /srv/stacks/petcare-companion/deploy/production/active-slot
echo "✅ Deployment complete! Active slot is now: $TARGET_SLOT"
```

---

## 🏷️ Image Tagging Patterns

| Environment     | Tag Pattern         | Example         | Notes                                |
| --------------- | ------------------- | --------------- | ------------------------------------ |
| **Development** | `dev-{shortsha}`    | `dev-4f31a8c`   | Short git commit SHA                 |
| **Staging**     | `staging-{version}` | `staging-1.2.3` | Semantic version                     |
| **Production**  | `release-{version}` | `release-1.2.3` | Same image as staging, different tag |

### Version Number Strategy

- Use **semantic versioning**: `MAJOR.MINOR.PATCH`
- Version source: Git tags, `VERSION` file, or release-please
- Example progression: `1.0.0` → `1.0.1` → `1.1.0` → `2.0.0`

---

## 🟦🟩 Blue/Green Deployment Details

### Container Naming

- **Blue slot:** `petcare_app_blue`, `petcare_web_blue`, etc.
- **Green slot:** `petcare_app_green`, `petcare_web_green`, etc.

### Traefik Configuration

- **Both slots use the same router names** (no slot suffix)
- **Only one slot has `traefik.enable=true` at a time**
- Traefik automatically routes to the enabled slot

Example labels (applied via compose when `TRAEFIK_ENABLE` toggles):

```yaml
labels:
  - 'traefik.enable=${TRAEFIK_ENABLE}'
  - 'traefik.http.routers.petcare-web.rule=Host(`${WEB_DOMAIN}`)'
  - 'traefik.http.routers.petcare-web.entrypoints=${TRAEFIK_ENTRYPOINT}'
  - 'traefik.http.routers.petcare-web.tls.certresolver=${TRAEFIK_CERT_RESOLVER}'
  - 'traefik.http.services.petcare-web.loadbalancer.server.port=80'
```

Network: both slots attach to the shared `traefik-proxy` network so Traefik can discover services.

### Shared Resources

- **Storage volume:** Both slots use external `storage` volume
- **Database/Redis:** Shared via external networks
- **Domain names:** Same domains for both slots (no user-facing difference)

### Benefits

- ✅ **Zero downtime** - New version starts before old one stops
- ✅ **Instant rollback** - Flip `TRAEFIK_ENABLE` back to old slot
- ✅ **Safe migrations** - Run on inactive slot, test, then switch
- ✅ **Staging parity** - Same image in staging and production

---

## 📁 Directory Structure

```
deploy/
├── development/
│   ├── docker-compose.yml       # Uses dev-{shortsha} tags
│   └── .env.example             # IMAGE_TAG=dev-{shortsha}
├── staging/
│   ├── docker-compose.yml       # Uses staging-{version} tags
│   └── .env.example             # IMAGE_TAG=staging (or staging-{version})
├── production/
│   ├── active-slot              # Tracks active slot: "blue" or "green"
│   ├── README.md                # Blue/green deployment guide
│   └── docker-compose.yml       # Legacy (deprecated, kept for reference)
├── production-blue/
│   ├── docker-compose.yml       # 🟦 Blue slot
│   └── .env.example             # SLOT=blue, TRAEFIK_ENABLE=false
└── production-green/
    ├── docker-compose.yml       # 🟩 Green slot
    └── .env.example             # SLOT=green, TRAEFIK_ENABLE=false
```

---

## 🔐 Environment Variables Reference

### Common Variables (All Environments)

```bash
ENV=develop|staging|prod
DOCKER_REGISTRY=ghcr.io/slightlyprivate  # Required, no default
BASE_DOMAIN=develop.petcare.ubuntu.slightlyprivate.com
WEB_DOMAIN=web.${BASE_DOMAIN}
UI_DOMAIN=ui.${BASE_DOMAIN}
PWA_DOMAIN=pwa.${BASE_DOMAIN}
IMAGE_TAG=dev-4f31a8c|staging-1.2.3|release-1.2.3
TRAEFIK_ENTRYPOINT=websecure
TRAEFIK_CERT_RESOLVER=cfresolver
```

### Production Blue/Green Only

```bash
SLOT=blue|green
TRAEFIK_ENABLE=true|false  # Controls active slot
```

---

## 🚦 Deployment Checklist

### Pre-Deployment

- [ ] Code merged to appropriate branch
- [ ] Tests passing in CI
- [ ] Database migrations reviewed
- [ ] Breaking changes documented

### Development

- [ ] Push to `develop` branch
- [ ] CI builds `dev-{shortsha}` images
- [ ] Auto-deploy to development environment
- [ ] Verify functionality

### Staging

- [ ] Push to `main` branch (or promote from develop)
- [ ] CI builds `staging-{version}` AND `release-{version}` tags
- [ ] Deploy to staging environment
- [ ] Run smoke tests
- [ ] Validate in staging

### Production

- [ ] Read `active-slot` file
- [ ] Deploy to **inactive** slot with `release-{version}` tag
- [ ] Run health checks
- [ ] Enable Traefik on new slot (`TRAEFIK_ENABLE=true`)
- [ ] Disable Traefik on old slot (`TRAEFIK_ENABLE=false`)
- [ ] Update `active-slot` file
- [ ] Monitor logs and metrics

### Rollback (if needed)

- [ ] Read current `active-slot` file
- [ ] Switch `TRAEFIK_ENABLE` back to previous slot
- [ ] Restart compose services to apply labels
- [ ] Update `active-slot` file to previous value

### Post-Deployment

- [ ] Hit primary endpoints/health URLs (set `SMOKE_URL` when running deploy script)
- [ ] Run migrations on active slot if needed:
      `docker compose -f deploy/production-$(cat deploy/production/active-slot)/docker-compose.yml exec app php artisan migrate --force`
- [ ] Refresh caches if needed: `php artisan config:cache` and `route:cache`
- [ ] Verify queues/Horizon/worker status
- [ ] Confirm logs are clean for the first 5–10 minutes

---

## 📝 Notes

- **Never delete old slots** - Keep both running for instant rollback
- **Storage is shared** - Migrations must be backwards compatible
- **Same image guarantee** - `staging-X` and `release-X` are identical
- **Slot tracking is critical** - Pipeline depends on `active-slot` file accuracy
