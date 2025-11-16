#!/usr/bin/env sh
set -euo pipefail

# Allow toggling Horizon runtime via env
if [ "${ENABLE_HORIZON:-false}" != "true" ]; then
  echo "[horizon] Disabled (set ENABLE_HORIZON=true to run). Sleeping..."
  exec tail -f /dev/null
fi

# Optional wait for DB (not strictly needed for Redis-only Horizon)
if [ "${WAIT_FOR_DB:-false}" = "true" ]; then
  php /var/www/html/wait-for-db.php || true
fi

# Ensure horizon command is available
if ! php artisan | grep -qE "\bhorizon(\s|:)"; then
  echo "[horizon] Command not found. Did you install laravel/horizon and run horizon:install? Sleeping..."
  exec tail -f /dev/null
fi

exec php artisan horizon
