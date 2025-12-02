# ✅ Docker Compose Configuration Refactor - Complete

## Summary of Changes

This refactor implements a production-grade CI/CD deployment strategy with environment
variable-based configuration and blue/green deployments.

## 🎯 Goals Achieved

### 1. ✅ Clean Traefik Label Management

**Before:**

```yaml
- 'traefik.http.routers.petcare_pwa_develop.rule=Host(`pwa.develop.petcare.ubuntu.slightlyprivate.com`)'
```

**After:**

```yaml
- 'traefik.http.routers.petcare_pwa_${ENV:-develop}.rule=Host(`${PWA_DOMAIN:-pwa.develop.petcare.ubuntu.slightlyprivate.com}`)'
```

**Benefits:**

- No hardcoded domains
- Easy to change per environment
- Sensible defaults included

### 2. ✅ Image Tag Strategy

| Environment     | Tag Pattern         | Example         | Purpose                           |
| --------------- | ------------------- | --------------- | --------------------------------- |
| **Development** | `dev-{shortsha}`    | `dev-4f31a8c`   | Fast iteration, traceable commits |
| **Staging**     | `staging-{version}` | `staging-1.2.3` | Testing release candidates        |
| **Production**  | `release-{version}` | `release-1.2.3` | Same image as staging             |

**Key Principle:** Build once for main branch, tag as both `staging-X` and `release-X`

### 3. ✅ Blue/Green Production Deployments

**Structure:**

```
deploy/
├── production/
│   ├── active-slot              # Tracks: "blue" or "green"
│   └── README.md
├── production-blue/
│   ├── docker-compose.yml       # 🟦 Blue slot
│   └── .env.example
└── production-green/
    ├── docker-compose.yml       # 🟩 Green slot
    └── .env.example
```

**How it works:**

1. CI reads `active-slot` file
2. Deploys to **inactive** slot
3. Runs health checks
4. Swaps Traefik labels (`TRAEFIK_ENABLE`)
5. Updates `active-slot` file

**Benefits:**

- Zero-downtime deployments
- Instant rollback capability
- Safe database migrations
- Same domains for both slots

## 📁 Updated Files

### Environment Examples

- `deploy/development/.env.example` - Added infrastructure vars, image tag pattern
- `deploy/staging/.env.example` - Added Traefik domains, updated image tag
- `deploy/production/.env.example` - Marked deprecated
- `deploy/production-blue/.env.example` - New blue/green config
- `deploy/production-green/.env.example` - New blue/green config

### Docker Compose Files

- `deploy/development/docker-compose.yml` - All hardcoded values → env vars
- `deploy/staging/docker-compose.yml` - Added labels, env vars, traefik-proxy network
- `deploy/production/docker-compose.yml` - Added labels, env vars (deprecated)
- `deploy/production-blue/docker-compose.yml` - New blue slot with `TRAEFIK_ENABLE`
- `deploy/production-green/docker-compose.yml` - New green slot with `TRAEFIK_ENABLE`

### Documentation & Scripts

- `deploy/DEPLOYMENT.md` - Comprehensive deployment guide
- `deploy/QUICK-REFERENCE.md` - Quick command reference
- `deploy/production/README.md` - Blue/green deployment guide
- `deploy/production/active-slot` - Slot tracker (starts with "blue")
- `scripts/deploy-production.sh` - Automated blue/green deployment script

## 🔑 Key Environment Variables

### All Environments

```bash
ENV=develop|staging|prod
BASE_DOMAIN=<env>.petcare.ubuntu.slightlyprivate.com
WEB_DOMAIN=web.${BASE_DOMAIN}
UI_DOMAIN=ui.${BASE_DOMAIN}
PWA_DOMAIN=pwa.${BASE_DOMAIN}
IMAGE_TAG=dev-{sha}|staging-{ver}|release-{ver}
TRAEFIK_ENTRYPOINT=websecure
TRAEFIK_CERT_RESOLVER=cfresolver
```

### Container Names

```bash
# Development/Staging
APP_CONTAINER_NAME=petcare_app_${ENV}
WEB_CONTAINER_NAME=petcare_web_${ENV}
...

# Production (includes slot)
SLOT=blue|green
APP_CONTAINER_NAME=petcare_app_${SLOT}
WEB_CONTAINER_NAME=petcare_web_${SLOT}
...
```

### Production Blue/Green Only

```bash
TRAEFIK_ENABLE=true|false  # Controls which slot receives traffic
```

## 🚀 Deployment Workflow

### Development

```bash
git push origin develop
# → CI builds dev-{shortsha}
# → Auto-deploys to development environment
```

### Staging

```bash
git push origin main
# → CI builds staging-{version} AND release-{version}
# → Auto-deploys staging-{version} to staging
# → Manual approval for production
```

### Production

```bash
# Manual or automated
./scripts/deploy-production.sh 1.2.3

# Result:
# 1. Reads active-slot → "blue"
# 2. Deploys to green slot (inactive)
# 3. Health checks green
# 4. Enables Traefik on green (TRAEFIK_ENABLE=true)
# 5. Disables Traefik on blue (TRAEFIK_ENABLE=false)
# 6. Updates active-slot → "green"
```

## 🎨 Labels Applied

All services now have:

```yaml
labels:
  - 'promtail.scrape=true' # Log collection
  - 'petcare.env=${ENV}' # Environment tracking
  - 'petcare.slot=${SLOT}' # Slot tracking (prod only)
  - 'traefik.enable=${TRAEFIK_ENABLE}' # Traffic routing
  - 'traefik.http.routers.*.rule=...' # Routing rules
  - 'traefik.http.routers.*.entrypoints=...' # HTTPS entrypoint
  - 'traefik.http.routers.*.tls.certresolver=...' # SSL certs
  - 'traefik.http.services.*.loadbalancer.server.port=...' # Backend port
```

## 🔄 Rollback Strategy

### Development/Staging

```bash
# Simply deploy previous version
export IMAGE_TAG=dev-abc123  # or staging-1.2.2
docker compose up -d --pull always
```

### Production

```bash
# Instant rollback: flip TRAEFIK_ENABLE back
CURRENT=$(cat deploy/production/active-slot)
PREVIOUS=$([[ "$CURRENT" == "blue" ]] && echo "green" || echo "blue")

cd deploy/production-$PREVIOUS
export TRAEFIK_ENABLE=true && docker compose up -d

cd ../production-$CURRENT
export TRAEFIK_ENABLE=false && docker compose up -d

echo "$PREVIOUS" > ../production/active-slot
```

## 📊 Before/After Comparison

### Before

- ❌ Hardcoded domains in compose files
- ❌ Inconsistent image tags (`develop`, `prod`)
- ❌ No labels on staging/production
- ❌ Single production slot (downtime on deploy)
- ❌ Manual configuration for each environment

### After

- ✅ Environment variables for all configuration
- ✅ Consistent image tagging strategy
- ✅ Complete labels (Traefik, Promtail, tracking)
- ✅ Blue/green deployments (zero downtime)
- ✅ Automated deployment scripts
- ✅ Comprehensive documentation

## 🎯 Best Practices Implemented

1. **Separation of concerns** - Config in .env, logic in compose
2. **Build once, deploy many** - Same image for staging/prod
3. **Zero-downtime deployments** - Blue/green strategy
4. **Traceability** - Commit SHAs in dev, versions in staging/prod
5. **Safety** - Inactive slot for testing before traffic switch
6. **Rollback capability** - Instant switch back to previous slot
7. **Documentation** - Clear guides for operations

## 🔗 Related Files

- `deploy/DEPLOYMENT.md` - Full deployment strategy
- `deploy/QUICK-REFERENCE.md` - Quick commands
- `deploy/production/README.md` - Blue/green details
- `scripts/deploy-production.sh` - Automated deployment

# Small Risks or Improvements

These are not problems — just areas where tightening up will make future you’s life easier.

1. **Ensure your .env.example files mirror all env vars exactly**  
   This is the main source of drift in multi-environment stacks.  
   You have the right variables, but make sure the example files contain every one of them:
   - TRAEFIK_ENABLE
   - SLOT
   - BASE_DOMAIN
   - IMAGE_TAG
   - ENTRYPOINT
   - CERT_RESOLVER
   - LOG labels  
     A missing var in .env.example becomes a silent misconfig later.

2. **Add CI-side validation**  
   You’ll want the CI pipeline to verify:
   - .env.example and .env keys match
   - the required variables exist before compose deploys
   - no stray hardcoded domains exist in compose files
   - slot name is valid (blue or green)  
     A simple CI script can prevent human error.

3. **Add a health check script per environment**  
   Your deploy script currently does checks, but consider a dedicated:  
   `scripts/health-check.sh`  
   This can:
   - hit the UI domain
   - hit the API /health route
   - hit Redis and DB
   - check container count
   - validate no errors in logs  
     Then your production script calls:  
     `./scripts/health-check.sh --slot green`  
     This will become important once you scale Slightly Better Luck.

4. **Add required versioning discipline**  
   You’re manually entering 1.2.3.  
   If you adopt semver keys in a VERSION file at repo root:  
   `VERSION=1.2.3`  
   Then your CI can:
   - read it
   - generate staging→prod artifacts automatically
   - prevent mismatches between tags and release notes  
     This helps future automation (release notes, changelogs, etc.).

5. **Scripts assume directory names — consider making them relative-safe**  
   For example:  
   `cd deploy/production-$INACTIVE_SLOT`  
   Instead, consider:

   ```bash
   SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
   PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
   ```

   Then navigating paths becomes bulletproof.

6. **Consider adding a Deployment Audit Log**  
   A simple file:  
   `deploy/deployment-history.log`  
   Every deploy script run can append:  
   `2025-12-01T03:00 → deployed release-1.2.3 to green, switched from blue`  
   This helps you debug and track production easily.

---

**Status:** ✅ Complete and ready for production use
