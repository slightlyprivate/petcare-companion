# Production Blue/Green Deployment

This directory tracks the active production deployment slot for blue/green deployments.

## Active Slot Tracking

The `active-slot` file contains either:

- `blue` - Blue slot is currently serving production traffic
- `green` - Green slot is currently serving production traffic

## How It Works

1. **CI/CD Pipeline reads this file** to determine which slot is currently active
2. **Deploys to the INACTIVE slot** (opposite of what's in the file)
3. **Runs health checks** on the newly deployed inactive slot
4. **Swaps Traefik labels** by updating the inactive slot's `.env` file:
   - Set `TRAEFIK_ENABLE=true` on the newly deployed slot
   - Set `TRAEFIK_ENABLE=false` on the old active slot
5. **Restarts the newly deployed slot** to apply Traefik label changes
6. **Updates this `active-slot` file** to reflect the new active slot

## Directory Structure

```
deploy/
├── production/
│   ├── active-slot           # Tracks which slot is active (blue or green)
│   └── docker-compose.yml    # Legacy single-slot deployment (deprecated)
├── production-blue/
│   ├── docker-compose.yml    # 🟦 Blue slot configuration
│   └── .env                  # Blue slot environment (SLOT=blue, TRAEFIK_ENABLE=true/false)
└── production-green/
    ├── docker-compose.yml    # 🟩 Green slot configuration
    └── .env                  # Green slot environment (SLOT=green, TRAEFIK_ENABLE=true/false)
```

## Manual Slot Switch

To manually switch slots:

```bash
# Check current active slot
cat /srv/stacks/petcare-companion/deploy/production/active-slot

# If blue is active, switch to green:
cd /srv/stacks/petcare-companion/deploy/production-green
sed -i 's/^TRAEFIK_ENABLE=.*/TRAEFIK_ENABLE=true/' .env
docker compose up -d

cd /srv/stacks/petcare-companion/deploy/production-blue
sed -i 's/^TRAEFIK_ENABLE=.*/TRAEFIK_ENABLE=false/' .env
docker compose up -d

echo "green" > /srv/stacks/petcare-companion/deploy/production/active-slot
```

## Key Configuration

Both slots use:

- **Same domains** in Traefik router rules (no slot suffix)
- **Different container names** (include slot suffix: `_blue` or `_green`)
- **Shared external storage volume** (`storage`)
- **`TRAEFIK_ENABLE` variable** controls which slot receives traffic

This ensures zero-downtime deployments with instant traffic switching.
