#!/usr/bin/env sh
set -euo pipefail

echo "[horizon] Preparing Laravel filesystem..."

# Ensure required directories exist
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

# Fix permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

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
