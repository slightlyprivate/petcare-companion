#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_FILE="${SCRIPT_DIR}/docker-compose.yml"

echo "Pulling latest develop images..."
docker compose -f "${COMPOSE_FILE}" pull

echo "Recreating services with minimal downtime..."
docker compose -f "${COMPOSE_FILE}" up -d --remove-orphans

echo "Pruning unused images..."
docker image prune -f --filter label=org.opencontainers.image.source=ghcr.io/slightlyprivate/petcare-companion || true

echo "Staging stack is up to date."
