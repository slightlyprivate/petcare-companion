#!/bin/bash

echo "[scheduler] Preparing Laravel filesystem..."
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

# Start scheduler
while true; do
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done