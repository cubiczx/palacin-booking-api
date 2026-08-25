# syntax=docker/dockerfile:1
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

FROM php:8.4-cli-alpine
RUN apk add --no-cache icu-dev libzip-dev oniguruma-dev postgresql-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql intl zip opcache

WORKDIR /var/www/app
COPY --from=vendor /app /var/www/app
RUN mkdir -p var && chmod -R 777 var

EXPOSE 10000
CMD sh -c "php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true && php -S 0.0.0.0:\${PORT:-10000} -t public"