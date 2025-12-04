# ==========================================
# Stage 1: Composer dependencies (PHP 8.3)
# ==========================================
FROM php:8.3-cli-alpine3.20 AS vendor

WORKDIR /app

# Pull the latest security patches (libxml2/openssl/curl/etc.)
RUN apk update && apk upgrade --no-cache --available

# Install system packages & PHP extensions for Composer
RUN apk add --no-cache \
    git \
    unzip \
    libzip \
    libzip-dev \
    zlib \
    zlib-dev \
    $PHPIZE_DEPS \
 && docker-php-ext-install zip pcntl \
 && apk del $PHPIZE_DEPS

# Install Composer manually (composer:2 image used PHP 8.5)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY src/composer.json src/composer.lock ./

# Install dependencies
RUN --mount=type=cache,target=/root/.composer \
    composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --no-progress \
    --no-interaction

# ==========================================
# Stage 2: PHP runtime (Production)
# ==========================================
FROM php:8.3-fpm-alpine3.20 AS runner

WORKDIR /var/www/html

# Pull the latest security patches (libxml2/openssl/curl/etc.)
RUN apk update && apk upgrade --no-cache --available

# Install PHP extensions
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    zlib \
    mysql-client \
 && apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zlib-dev \
    $PHPIZE_DEPS \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql pcntl gd zip \
 && apk del .build-deps

# Copy from builder stages
COPY --from=vendor /app/vendor ./vendor

# Copy application source
COPY src/ ./

# Create storage directories
RUN mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/app/entrypoints/*.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

EXPOSE 9000

USER www-data

# ==========================================
# Stage 3: Development runtime (with Composer)
# ==========================================
FROM runner AS dev

USER root
# Install Composer and git for development
RUN apk add --no-cache git unzip
COPY --from=vendor /usr/bin/composer /usr/bin/composer

USER www-data
