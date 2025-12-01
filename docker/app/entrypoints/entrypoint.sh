#!/bin/sh

# Fix permissions every container start
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

php-fpm
