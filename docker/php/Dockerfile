FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git curl libpng-dev libzip-dev zip unzip \
    && docker-php-ext-install pdo_mysql zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
