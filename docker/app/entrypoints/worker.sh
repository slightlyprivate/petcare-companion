#!/usr/bin/env sh
set -euo pipefail

echo "[worker] Preparing Laravel filesystem..."

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

# Optional wait for DB in dev/local only
if [ "${WAIT_FOR_DB:-false}" = "true" ]; then
  php /var/www/html/wait-for-db.php
fi

exec php artisan queue:work --sleep=3 --tries=3 --backoff=5 --memory=256
