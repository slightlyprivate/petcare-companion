#!/bin/sh
set -e

# =============================================================================
# Laravel Container Entrypoint
# =============================================================================
# This script handles:
# 1. Storage directory creation and permissions (runs as root if needed)
# 2. Laravel cache management
# 3. Database migrations
# 4. Starting PHP-FPM (which drops to www-data internally)
# =============================================================================

echo "[entrypoint] Preparing Laravel filesystem..."

# Ensure required directories exist
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

# Fix ownership - handles mounted volumes from host with mismatched UIDs
# Only attempt if running as root (UID 0)
if [ "$(id -u)" = "0" ]; then
  echo "[entrypoint] Running as root, fixing storage permissions..."
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

  # Run Laravel commands as www-data
  echo "[entrypoint] Clearing stale Laravel caches..."
  su-exec www-data php artisan config:clear || true
  su-exec www-data php artisan cache:clear || true
  su-exec www-data php artisan route:clear || true
  su-exec www-data php artisan view:clear || true

  echo "[entrypoint] Rebuilding Laravel caches..."
  su-exec www-data php artisan config:cache || true
  su-exec www-data php artisan route:cache || true
  su-exec www-data php artisan view:cache || true

  echo "[entrypoint] Running migrations..."
  su-exec www-data php artisan migrate --force

  # PHP-FPM must start as root - it handles dropping to www-data internally
  # via its pool configuration (user = www-data, group = www-data)
  echo "[entrypoint] Starting PHP-FPM..."
  exec php-fpm
else
  # Already running as non-root (www-data), proceed normally
  echo "[entrypoint] Running as $(whoami), proceeding with Laravel setup..."

  echo "[entrypoint] Clearing stale Laravel caches..."
  php artisan config:clear || true
  php artisan cache:clear || true
  php artisan route:clear || true
  php artisan view:clear || true

  echo "[entrypoint] Rebuilding Laravel caches..."
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true

  # Run migrations
  php artisan migrate --force

  echo "[entrypoint] Starting PHP-FPM..."
  exec php-fpm
fi
