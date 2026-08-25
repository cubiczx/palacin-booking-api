# syntax=docker/dockerfile:1

##### Stage 1: PHP dependencies (build) #####
FROM composer:2 AS composer_build

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

##### Stage 2: runtime PHP-FPM #####
FROM php:8.4-fpm-alpine AS php

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo_mysql \
        intl \
        zip \
        opcache

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www/app

COPY --from=composer_build /app /var/www/app

RUN addgroup -g 1000 app \
    && adduser -D -u 1000 -G app app \
    && chown -R app:app /var/www/app/var

USER app

EXPOSE 9000
CMD ["php-fpm"]

##### Stage 3: Nginx #####
FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=composer_build /app/public /var/www/app/public

EXPOSE 80