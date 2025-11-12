# docker/app.Dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev libicu-dev libpq-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl bcmath zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ARG UID=1000
ARG GID=1000

# create group/user matching host ids
RUN groupadd -g $GID app || true \
 && useradd -m -u $UID -g $GID app || true

# make sure project dir exists and is owned
RUN mkdir -p /var/www/html && chown -R app:app /var/www/html

# run php-fpm as the same user to avoid perms mismatch
RUN sed -ri 's/^user = .*/user = app/; s/^group = .*/group = app/; \
             s/^;?listen.owner.*/listen.owner = app/; \
             s/^;?listen.group.*/listen.group = app/' /usr/local/etc/php-fpm.d/www.conf

USER app
WORKDIR /var/www/html
