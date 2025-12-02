# 🚀 Quick Deployment Reference

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
cd /srv/stacks/petcare-companion/deploy/development
export DOCKER_REGISTRY=ghcr.io/slightlyprivate
export IMAGE_TAG=dev-4f31a8c
docker compose up -d --pull always
```

### Staging

```bash
cd /srv/stacks/petcare-companion/deploy/staging
export DOCKER_REGISTRY=ghcr.io/slightlyprivate
export IMAGE_TAG=staging-1.2.3
docker compose up -d --pull always
```

### Production (Blue/Green)

```bash
# Automated deployment (requires DOCKER_REGISTRY)
export DOCKER_REGISTRY=ghcr.io/slightlyprivate
/srv/stacks/petcare-companion/scripts/deploy-production.sh 1.2.3

# Manual deployment
export DOCKER_REGISTRY=ghcr.io/slightlyprivate
ACTIVE=$(cat deploy/production/active-slot)
TARGET=$([[ "$ACTIVE" == "blue" ]] && echo "green" || echo "blue")

cd /srv/stacks/petcare-companion/deploy/production-$TARGET
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
export DOCKER_REGISTRY=ghcr.io/slightlyprivate
CURRENT=$(cat deploy/production/active-slot)
PREVIOUS=$([[ "$CURRENT" == "blue" ]] && echo "green" || echo "blue")

cd deploy/production-$PREVIOUS
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
