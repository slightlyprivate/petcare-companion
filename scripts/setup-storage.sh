#!/bin/bash
set -e

STORAGE_PATH="${STORAGE_PATH:-/mnt/data/appdata/petcare-storage-staging}"

# Create directory if missing
sudo mkdir -p "$STORAGE_PATH"

# Set ownership to www-data (UID 33)
sudo chown -R 33:33 "$STORAGE_PATH"

# Set permissions (775 for dirs, 664 for files if any)
sudo chmod -R 775 "$STORAGE_PATH"

# Create Laravel subdirs
sudo -u www-data mkdir -p "$STORAGE_PATH/app/public"
sudo -u www-data mkdir -p "$STORAGE_PATH/framework/cache"
sudo -u www-data mkdir -p "$STORAGE_PATH/framework/sessions"
sudo -u www-data mkdir -p "$STORAGE_PATH/framework/views"
sudo -u www-data mkdir -p "$STORAGE_PATH/logs"

echo "Storage setup complete."