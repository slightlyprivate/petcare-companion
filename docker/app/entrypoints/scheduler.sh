#!/bin/sh
set -e

# =============================================================================
# Laravel Scheduler Entrypoint
# =============================================================================

echo "[scheduler] Preparing Laravel filesystem..."
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

# Fix ownership if running as root
if [ "$(id -u)" = "0" ]; then
  echo "[scheduler] Running as root, fixing storage permissions..."
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
  
  echo "[scheduler] Starting scheduler as www-data..."
  exec su-exec www-data sh -c '
    while true; do
        php artisan schedule:run --verbose --no-interaction
        sleep 60
    done
  '
else
  echo "[scheduler] Starting scheduler as $(whoami)..."
  while true; do
      php artisan schedule:run --verbose --no-interaction
      sleep 60
  done
fi