#!/bin/sh
set -e

# =============================================================================
# Laravel Queue Worker Entrypoint
# =============================================================================

echo "[worker] Preparing Laravel filesystem..."

# Ensure required directories exist
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

# Fix ownership if running as root
if [ "$(id -u)" = "0" ]; then
  echo "[worker] Running as root, fixing storage permissions..."
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
  
  echo "[worker] Starting queue worker as www-data..."
  exec su-exec www-data php artisan queue:work --sleep=3 --tries=3 --backoff=5 --memory=256
else
  echo "[worker] Starting queue worker as $(whoami)..."
  exec php artisan queue:work --sleep=3 --tries=3 --backoff=5 --memory=256
fi
