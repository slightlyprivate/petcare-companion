#!/bin/bash
set -e

# =============================================================================
# Storage Setup Script for PetCare Companion
# =============================================================================
# This script prepares the host storage directory for Laravel.
# 
# IMPORTANT: This script only creates directories and sets broad permissions.
# The container entrypoint handles UID/GID ownership (which varies by distro).
#
# Usage:
#   STORAGE_PATH=/mnt/data/appdata/petcare-storage-staging ./setup-storage.sh
# =============================================================================

STORAGE_PATH="${STORAGE_PATH:-/mnt/data/appdata/petcare-storage-staging}"

echo "Setting up Laravel storage at: $STORAGE_PATH"

# Create base directory if missing
sudo mkdir -p "$STORAGE_PATH"

# Create Laravel subdirs
sudo mkdir -p "$STORAGE_PATH/app/public"
sudo mkdir -p "$STORAGE_PATH/framework/cache"
sudo mkdir -p "$STORAGE_PATH/framework/sessions"
sudo mkdir -p "$STORAGE_PATH/framework/views"
sudo mkdir -p "$STORAGE_PATH/logs"

# Set permissions to be world-writable (container will fix ownership at startup)
# This ensures the container can always write, regardless of UID mismatch
sudo chmod -R 777 "$STORAGE_PATH"

echo "✅ Storage setup complete."
echo ""
echo "Note: The container entrypoint will automatically fix ownership"
echo "      to match the container's www-data UID at startup."