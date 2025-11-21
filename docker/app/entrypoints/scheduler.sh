#!/usr/bin/env sh
set -euo pipefail

if [ "${WAIT_FOR_DB:-false}" = "true" ]; then
  php /var/www/html/wait-for-db.php
fi

exec php artisan schedule:work
