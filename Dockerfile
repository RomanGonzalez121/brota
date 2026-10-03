# Image for showing the mockup online (see README, "Publicarlo con un link").
# It is a demo setup: SQLite, PHP's own server, data re-seeded on every start.

# ---- Front-end assets ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Application ----
FROM php:8.4-cli
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dependencies first, so they are only reinstalled when composer.lock changes.
# Dev packages stay in: the seeders need Faker to build the demo data.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --optimize && chmod +x docker/start.sh

ENV APP_NAME="Brotá" \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_LOCALE=es \
    APP_FALLBACK_LOCALE=es \
    APP_FAKER_LOCALE=es_AR \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/app/database/database.sqlite \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync \
    PHP_CLI_SERVER_WORKERS=4

EXPOSE 10000
CMD ["docker/start.sh"]
