#!/bin/bash
# Blue/Green Deployment Helper Script
# Usage: ./deploy-production.sh <version>
# Example: ./deploy-production.sh 1.2.3

set -e

VERSION="${1}"
if [ -z "$VERSION" ]; then
    echo "❌ Error: Version required"
    echo "Usage: $0 <version>"
    echo "Example: $0 1.2.3"
    exit 1
fi

# Require DOCKER_REGISTRY environment variable
if [ -z "$DOCKER_REGISTRY" ]; then
    echo "❌ Error: DOCKER_REGISTRY environment variable must be set"
    echo "Example: export DOCKER_REGISTRY=ghcr.io/slightlyprivate"
    exit 1
fi

IMAGE_TAG="release-${VERSION}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
DEPLOY_ROOT="${PROJECT_ROOT}/deploy"
ACTIVE_SLOT_FILE="${DEPLOY_ROOT}/production/active-slot"

echo "🚀 PetCare Production Deployment"
echo "================================"
echo "Version: ${VERSION}"
echo "Registry: ${DOCKER_REGISTRY}"
echo "Image Tag: ${IMAGE_TAG}"
echo ""

# Check if active-slot file exists
if [ ! -f "$ACTIVE_SLOT_FILE" ]; then
    echo "❌ Error: active-slot file not found at ${ACTIVE_SLOT_FILE}"
    exit 1
fi

# Read current active slot
ACTIVE_SLOT=$(cat "$ACTIVE_SLOT_FILE" | tr -d '[:space:]')
echo "📍 Current active slot: ${ACTIVE_SLOT}"

# Determine target slot (opposite of active)
if [ "$ACTIVE_SLOT" = "blue" ]; then
    TARGET_SLOT="green"
    TARGET_EMOJI="🟩"
elif [ "$ACTIVE_SLOT" = "green" ]; then
    TARGET_SLOT="blue"
    TARGET_EMOJI="🟦"
else
    echo "❌ Error: Invalid active slot value: ${ACTIVE_SLOT}"
    echo "   Expected 'blue' or 'green'"
    exit 1
fi

echo "🎯 Target slot for deployment: ${TARGET_EMOJI} ${TARGET_SLOT}"
echo ""

# Confirm deployment
read -p "Continue with deployment to ${TARGET_SLOT} slot? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Deployment cancelled"
    exit 0
fi

TARGET_DIR="${DEPLOY_ROOT}/production-${TARGET_SLOT}"
cd "$TARGET_DIR"

echo ""
echo "📦 Step 1: Pulling images..."
echo "   Registry: ${DOCKER_REGISTRY}"
echo "   Image: ${IMAGE_TAG}"
export IMAGE_TAG
export DOCKER_REGISTRY
docker compose pull

echo ""
echo "🔧 Step 2: Deploying to ${TARGET_SLOT} slot (inactive)..."
export TRAEFIK_ENABLE="false"
docker compose up -d

echo ""
echo "⏳ Step 3: Waiting for services to become healthy (30s)..."
sleep 30

echo ""
echo "🏥 Step 4: Health check..."
HEALTH_STATUS=$(docker compose ps --format json | jq -r 'if type == "array" then .[].Health else .Health end' | grep -c "healthy" || echo "0")
TOTAL_SERVICES=$(docker compose ps --format json | jq -r 'if type == "array" then . | length else 1 end')
if [ "$HEALTH_STATUS" -lt "$TOTAL_SERVICES" ]; then
    echo "❌ Health check failed. Some services are not healthy:"
    docker compose ps
    echo ""
    echo "Check logs with: cd ${TARGET_DIR} && docker compose logs"
    exit 1
fi
echo "✅ All services healthy"

echo ""
echo "🔀 Step 5: Activating ${TARGET_SLOT} slot in Traefik..."
export TRAEFIK_ENABLE="true"
docker compose up -d

echo ""
echo "⏸️  Step 6: Deactivating ${ACTIVE_SLOT} slot..."
ACTIVE_DIR="${DEPLOY_ROOT}/production-${ACTIVE_SLOT}"
cd "$ACTIVE_DIR"
export TRAEFIK_ENABLE="false"
docker compose up -d

echo ""
echo "💾 Step 7: Updating active-slot tracker..."
echo "$TARGET_SLOT" > "$ACTIVE_SLOT_FILE"

echo ""
echo "✅ Deployment complete!"
echo ""
echo "Summary:"
echo "  🎉 Version ${VERSION} is now live"
echo "  ${TARGET_EMOJI} Active slot: ${TARGET_SLOT}"
echo "  📁 Deployed to: ${TARGET_DIR}"
echo ""
echo "Quick rollback (if needed):"
echo "  cd ${ACTIVE_DIR}"
echo "  export TRAEFIK_ENABLE=true && docker compose up -d"
echo "  cd ${TARGET_DIR}"
echo "  export TRAEFIK_ENABLE=false && docker compose up -d"
echo "  echo '${ACTIVE_SLOT}' > ${ACTIVE_SLOT_FILE}"
echo ""
