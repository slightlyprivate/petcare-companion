# 🚀 Quick Deployment Reference

> **⚠️ IMPORTANT: These are example commands only!**
>
> This cheatsheet provides **educational examples** of deployment commands. The actual deployments
> for PetCare Companion are managed in a separate repository:
> [`homelab-slightly-server`](https://github.com/slightlyprivate/homelab-slightly-server). Paths and
> registries shown are illustrative; adapt them to your environment.

## Image Tag Patterns

| Environment | Pattern             | Example         | Registry             |
| ----------- | ------------------- | --------------- | -------------------- |
| Development | `dev-{7-char-sha}`  | `dev-4f31a8c`   | `${DOCKER_REGISTRY}` |
| Staging     | `staging-{version}` | `staging-1.2.3` | `${DOCKER_REGISTRY}` |
| Production  | `release-{version}` | `release-1.2.3` | `${DOCKER_REGISTRY}` |

**Note:** `DOCKER_REGISTRY` must be explicitly set (e.g., `ghcr.io/slightlyprivate`). No default
provided.

## Deployment Commands

### Development

```bash
cd <your-deploy-path>/deploy/development
export DOCKER_REGISTRY=<your-registry>
export IMAGE_TAG=dev-4f31a8c
docker compose up -d --pull always
```

### Staging

```bash
cd <your-deploy-path>/deploy/staging
export DOCKER_REGISTRY=<your-registry>
export IMAGE_TAG=staging
docker compose up -d --pull always
```

### Production (Blue/Green)

Watchtower is disabled on production slots; use the script below to promote after staging
validation.

```bash
# Automated deployment (requires DOCKER_REGISTRY)
export DOCKER_REGISTRY=<your-registry>
<your-scripts-path>/deploy-production.sh 1.2.3

# Manual deployment
export DOCKER_REGISTRY=<your-registry>
ACTIVE=$(cat <your-deploy-path>/deploy/production/active-slot)
TARGET=$([[ "$ACTIVE" == "blue" ]] && echo "green" || echo "blue")

cd <your-deploy-path>/deploy/production-$TARGET
export IMAGE_TAG=release-1.2.3
export TRAEFIK_ENABLE=false
docker compose up -d --pull always

# Wait for health checks
sleep 30

# Activate new slot
export TRAEFIK_ENABLE=true
docker compose up -d

# Deactivate old slot
cd ../production-$ACTIVE
export TRAEFIK_ENABLE=false
docker compose up -d

# Update tracker
echo "$TARGET" > ../production/active-slot
```

## Rollback

```bash
# Quick rollback (swap slots back)
export DOCKER_REGISTRY=<your-registry>
CURRENT=$(cat <your-deploy-path>/deploy/production/active-slot)
PREVIOUS=$([[ "$CURRENT" == "blue" ]] && echo "green" || echo "blue")

cd <your-deploy-path>/deploy/production-$PREVIOUS
export TRAEFIK_ENABLE=true && docker compose up -d

cd ../production-$CURRENT
export TRAEFIK_ENABLE=false && docker compose up -d

echo "$PREVIOUS" > ../production/active-slot
```

## CI/CD Pipeline Flow

```
develop → dev-{sha} → Development Environment
   ↓
  main → staging-{ver} + release-{ver} → Staging Environment
                           ↓
                      (manual approval)
                           ↓
                    Production Blue/Green
```

## Key Files

- `deploy/production/active-slot` - Current active production slot
- `deploy/production-blue/.env` - Blue slot configuration
- `deploy/production-green/.env` - Green slot configuration
- `deploy/DEPLOYMENT.md` - Full deployment documentation
- `scripts/deploy-production.sh` - Automated blue/green deployment

## Health Checks

```bash
# Check service status
docker compose ps

# View logs
docker compose logs -f

# Check which slot is active
cat deploy/production/active-slot

# Check Traefik labels
docker inspect petcare_web_blue | jq '.[].Config.Labels' | grep traefik
```
