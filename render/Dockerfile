# syntax=docker/dockerfile:1
FROM composer:2 AS vendor

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

FROM php:8.4-cli-alpine

RUN apk add --no-cache icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql intl zip opcache

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/app

COPY --from=vendor /app /var/www/app

RUN mkdir -p var && chmod -R 777 var

EXPOSE 10000

# Render te da el puerto en $PORT
CMD sh -c "php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true && php -S 0.0.0.0:\${PORT:-10000} -t public"