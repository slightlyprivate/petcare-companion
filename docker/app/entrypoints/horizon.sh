#!/bin/sh
set -e

# =============================================================================
# Laravel Horizon Entrypoint
# =============================================================================

echo "[horizon] Preparing Laravel filesystem..."
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

# Fix ownership if running as root
if [ "$(id -u)" = "0" ]; then
  echo "[horizon] Running as root, fixing storage permissions..."
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
  
  echo "[horizon] Starting Horizon as www-data..."
  exec su-exec www-data php artisan horizon
else
  echo "[horizon] Starting Horizon as $(whoami)..."
  exec php artisan horizon
fi