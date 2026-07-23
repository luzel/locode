# syntax=docker/dockerfile:1

FROM node:20-bookworm-slim AS assets
WORKDIR /app

COPY app/package.json app/package-lock.json app/webpack.config.js ./
COPY app/assets ./assets
COPY app/public ./public

RUN npm ci && npm run build

FROM dunglas/frankenphp:1-php8.4 AS php-base

RUN install-php-extensions intl opcache pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

FROM php-base AS vendor

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    COMPOSER_ALLOW_SUPERUSER=1

COPY app/composer.json app/composer.lock app/symfony.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

FROM php-base AS runtime

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    SERVER_NAME=:80

WORKDIR /app

COPY app/ ./
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY docker/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/locode-entrypoint

RUN chmod +x /usr/local/bin/locode-entrypoint \
    && mkdir -p var/cache var/log \
    && chmod -R 0777 var

EXPOSE 80

ENTRYPOINT ["locode-entrypoint"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
