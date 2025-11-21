#!/usr/bin/env sh
set -euo pipefail

# Optional wait for DB in dev/local only
if [ "${WAIT_FOR_DB:-false}" = "true" ]; then
  php /var/www/html/wait-for-db.php
fi

exec php artisan queue:work --sleep=3 --tries=3 --backoff=5 --memory=256
