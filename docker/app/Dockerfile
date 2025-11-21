# ==========================================
# Stage 1: Composer dependencies (PHP 8.3)
# ==========================================
FROM php:8.3-cli-alpine AS vendor

WORKDIR /app

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
RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --no-progress \
    --no-interaction

# ==========================================
# Stage 2: PHP runtime (Alpine OK here)
# ==========================================
FROM php:8.3-fpm-alpine AS runner

WORKDIR /var/www/html

# Install PHP extensions
RUN apk add --no-cache \
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    freetype \
    freetype-dev \
    libzip \
    libzip-dev \
    zlib \
    zlib-dev \
    mysql-client \
 && apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql pcntl gd zip \
 && apk del .phpize-deps

# Copy from builder stages
COPY --from=vendor /app/vendor ./vendor

# Copy application source
COPY src/ ./

# Create storage directories
RUN mkdir -p storage/framework/{cache,sessions,views} \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/app/entrypoints/*.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

EXPOSE 9000
