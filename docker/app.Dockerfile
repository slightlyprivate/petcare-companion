# docker/app.Dockerfile
# =============================================================================
# Multi-stage build for optimized Laravel production image
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Builder - Install dependencies and compile assets
# -----------------------------------------------------------------------------
FROM serversideup/php:8.3-fpm-alpine AS builder

# Install build dependencies
USER root
RUN apk add --no-cache \
    git \
    unzip \
    nodejs \
    npm

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY --chown=www-data:www-data composer.json composer.lock ./

# Install Composer dependencies (production only)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# Copy application source
COPY --chown=www-data:www-data . .

# Copy frontend package files
COPY --chown=www-data:www-data package*.json ./

# Install and build frontend assets
RUN npm ci --quiet && npm run build

# Run Laravel optimizations
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# -----------------------------------------------------------------------------
# Stage 2: Runner - Minimal production runtime
# -----------------------------------------------------------------------------
FROM serversideup/php:8.3-fpm-alpine AS runner

# Set production PHP configuration
ENV PHP_DISPLAY_ERRORS=Off \
    PHP_MEMORY_LIMIT=256M \
    PHP_MAX_EXECUTION_TIME=60 \
    PHP_POST_MAX_SIZE=10M \
    PHP_UPLOAD_MAX_FILE_SIZE=10M \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=128 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=10000 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

USER root

# Install only runtime dependencies (no build tools)
RUN apk add --no-cache \
    mysql-client \
    && docker-php-serversideup-dep-install-alpine "redis"

WORKDIR /var/www/html

# Copy application from builder
COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint scripts
COPY --chown=www-data:www-data docker/entrypoints/*.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

# Healthcheck using Laravel's built-in status
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php artisan inspire > /dev/null 2>&1 || exit 1

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
