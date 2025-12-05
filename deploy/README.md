# Deployment Documentation

Environment-specific Docker Compose configurations for the PetCare Companion application with
production-grade CI/CD and blue/green deployment support.

## 📚 Documentation

Choose the guide that fits your needs:

### 📖 [DEPLOYMENT.md](./DEPLOYMENT.md)

**Comprehensive deployment guide** for operations and engineering teams.

Covers:

- Complete CI/CD pipeline details
- Blue/green production architecture
- First-time environment setup
- Deployment procedures and workflows
- Health check configuration
- Security hardening
- Troubleshooting guide

**Use this when**: Setting up new environments, learning the deployment system, or troubleshooting
issues.

### ⚡ [QUICK-REFERENCE.md](./QUICK-REFERENCE.md)

**Command cheatsheet** for daily operations.

Includes:

- Fast setup commands
- Deployment one-liners
- Status check commands
- Log viewing shortcuts
- Emergency procedures
- Useful aliases

**Use this when**: You need to quickly deploy, check status, view logs, or perform common tasks.

## 🎯 Quick Start

### I want to

>**Deploy to production**

```bash
./scripts/deploy-production.sh 1.2.3
```

📖 Details: [DEPLOYMENT.md - Production Procedures](./DEPLOYMENT.md#-production-deployment-procedures)

>**Set up a new environment**

```bash
cd deploy/{environment}
cp .env.example .env && nano .env
docker compose up -d
```

📖 Details: [DEPLOYMENT.md - First-Time Setup](./DEPLOYMENT.md#-first-time-environment-setup)

>**Check what's running**

```bash
cat deploy/production/active-slot
docker compose -f deploy/production-$(cat deploy/production/active-slot)/docker-compose.yml ps
```

⚡ More: [QUICK-REFERENCE.md - Status Checks](./QUICK-REFERENCE.md#-status-checks)

>**View logs**

```bash
ACTIVE=$(cat deploy/production/active-slot)
docker compose -f deploy/production-$ACTIVE/docker-compose.yml logs -f
```

⚡ More: [QUICK-REFERENCE.md - Logs](./QUICK-REFERENCE.md#-logs)

>**Rollback production**

```bash
./scripts/deploy-production.sh 1.2.2  # previous version
```

📖 Details: [DEPLOYMENT.md - Emergency Rollback](./DEPLOYMENT.md#emergency-rollback)

**Troubleshoot issues**
📖 See: [DEPLOYMENT.md - Troubleshooting Guide](./DEPLOYMENT.md#-troubleshooting-guide)

## 📁 Directory Structure

```sh
deploy/
├── README.md              ← You are here (index)
├── DEPLOYMENT.md          ← Comprehensive operations guide
├── QUICK-REFERENCE.md     ← Command cheatsheet
│
├── development/           ← Dev environment (auto-deploy via Watchtower)
│   ├── docker-compose.yml
│   └── .env.example
│
├── staging/               ← Staging environment (auto-deploy via Watchtower)
│   ├── docker-compose.yml
│   └── .env.example
│
├── production/            ← Legacy tracker (deprecated single-slot compose)
│   ├── active-slot        ← Tracks active production slot (blue|green)
│   └── README.md
│
├── production-blue/       ← Production blue slot
│   ├── docker-compose.yml
│   └── .env.example
│
└── production-green/      ← Production green slot
    ├── docker-compose.yml
    └── .env.example
```

## 🚀 Deployment Strategy Overview

### Development

- **Trigger**: Push to `develop` branch
- **Tag**: `develop` (moving) + `dev-{shortsha}` (e.g., `dev-4f31a8c`)
- **Deploy**: Automated via Watchtower (external service)
- **Timeline**: ~5-10 minutes

### Staging

- **Trigger**: Push to `main` branch
- **Tag**: `staging` (moving) + `staging-{version}` (e.g., `staging-1.2.3`)
- **Deploy**: Automated via Watchtower (external service)
- **Timeline**: ~5-10 minutes

### Production

- **Trigger**: Manual via `scripts/deploy-production.sh`
- **Tag**: `release-{version}` (e.g., `release-1.2.3`)
- **Deploy**: Blue/green with health checks and traffic switch
- **Timeline**: ~3-5 minutes
- **Key Feature**: Zero downtime, instant rollback
- **Safety**: Watchtower disabled on production slots (manual promotion only)

**Note**: `staging-{version}` and `release-{version}` point to the same image (build once, deploy
twice).

## 🟦🟩 Blue/Green Production

Production uses two identical slots for zero-downtime deployments:

```sh
┌─────────────────┐
│  Active: Blue   │ ← Receives traffic (TRAEFIK_ENABLE=true)
│  Inactive: Green│ ← No traffic (TRAEFIK_ENABLE=false)
└─────────────────┘

    Deploy v1.2.3 to Green
    Health check Green
    Switch traffic to Green
    Blue becomes rollback option

┌─────────────────┐
│  Inactive: Blue │ ← Previous version (instant rollback)
│  Active: Green  │ ← New version receiving traffic
└─────────────────┘
```

**Benefits**: Zero downtime, instant rollback, safe deployments

📖 Full details: [DEPLOYMENT.md - Blue/Green Architecture](./DEPLOYMENT.md#-bluegreen-production-architecture)

## 🏷️ Image Tags

| Environment | Tag Example | Source Branch | Auto-Deploy |
| ----------- | ----------- | ------------- | ----------- |
| Development | `dev-4f31a8c` | `develop` | ✅ Watchtower |
| Staging | `staging-1.2.3` | `main` | ✅ Watchtower |
| Production | `release-1.2.3` | `main` | ❌ Manual |

All images in `ghcr.io/slightlyprivate/petcare-companion-*` registry.

## 🔐 Security

Production environments include:

- Non-root containers with minimal tmpfs mounts
- Read-only filesystems where possible
- Traefik TLS via Cloudflare DNS challenge
- Dedicated external networks for DB/Redis
- Shared storage volume managed on the host

## 🧠 Tips

- Keep `deploy/production/active-slot` accurate; all tooling relies on it.
- Run migrations on the inactive slot before traffic switch.
- Use `docker compose ps` and `docker compose logs` with `production-$(cat active-slot)` to inspect
  the active slot quickly.
