# Production Directory

## ⚠️ DEPRECATION NOTICE

**This directory structure is deprecated and kept for reference only.**

For production deployments, use the **blue/green deployment slots**:

- [`production-blue/`](../production-blue/) - Blue slot for zero-downtime deployments
- [`production-green/`](../production-green/) - Green slot for zero-downtime deployments

Watchtower labels are disabled on production slots; promotion is always manual after staging is
validated.

## 📁 Current Contents

This directory contains only:

- **`active-slot`** - Tracks which production slot (blue or green) is currently active and receiving
  traffic
- **`README.md`** - This deprecation notice (you are here)

## 🟦🟩 Using Blue/Green Deployments

### Quick Start

Deploy to production using the automated script:

```bash
./scripts/deploy-production.sh 1.2.3
```

The script will:

1. Read the `active-slot` file to determine current active slot
2. Deploy new version to the inactive slot
3. Run health checks
4. Switch Traefik traffic to the new slot
5. Update the `active-slot` file

### Manual Operations

Check active slot:

```bash
cat deploy/production/active-slot
```

View active slot containers:

```bash
ACTIVE=$(cat deploy/production/active-slot)
docker compose -f deploy/production-$ACTIVE/docker-compose.yml ps
```

View logs from active slot:

```bash
ACTIVE=$(cat deploy/production/active-slot)
docker compose -f deploy/production-$ACTIVE/docker-compose.yml logs -f
```

## 📚 Documentation

For complete production deployment documentation, see:

- **[DEPLOYMENT.md](../DEPLOYMENT.md)** - Comprehensive deployment guide
- **[QUICK-REFERENCE.md](../QUICK-REFERENCE.md)** - Command cheatsheet

## 🔄 Migration from Legacy

If you have an existing deployment using the old `deploy/prod/` structure:

1. **Stop the old deployment** (choose a maintenance window)

   ```bash
   cd deploy/prod && docker compose down
   ```

2. **Set up blue/green slots** following [DEPLOYMENT.md - First-Time Setup](../DEPLOYMENT.md#production-first-time-setup)

3. **Deploy current version to both slots**

   ```bash
   # Blue slot (active)
   cd deploy/production-blue
   cp .env.example .env
   # Edit .env: set IMAGE_TAG=release-{current-version}, TRAEFIK_ENABLE=true
   docker compose up -d

   # Green slot (inactive)
   cd ../production-green
   cp .env.example .env
   # Edit .env: set IMAGE_TAG=release-{current-version}, TRAEFIK_ENABLE=false
   docker compose up -d

   # Initialize active slot tracker
   echo "blue" > ../production/active-slot
   ```

4. **Test deployment script**

   ```bash
   ./scripts/deploy-production.sh {next-version}
   ```

## 📞 Support

If you need help migrating from the legacy deployment structure, see:

- Ask in #engineering Slack channel
