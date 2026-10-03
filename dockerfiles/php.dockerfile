FROM php:8.5-fpm

ARG UID=1000
ARG GID=1000
ARG INSTALL_XDEBUG=true

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo_mysql zip \
    && if [ "$INSTALL_XDEBUG" = "true" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Run PHP-FPM and CLI as the host user so files created in bind mounts are not root-owned
RUN groupmod -o -g ${GID} www-data && usermod -o -u ${UID} -g ${GID} www-data

USER www-data

WORKDIR /var/www/html/laravel
