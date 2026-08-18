# syntax=docker/dockerfile:1

# ── Stage 1: PHP dependencies ────────────────────────────────
# ── Stage 1: PHP dependencies ────────────────────────────────
# NOTE: intentionally installing WITHOUT --no-dev. The seeders used to reset
# demo data on every boot rely on fakerphp/faker, which ships as a dev
# dependency in this project's composer.json. Keeping dev deps is a small
# tradeoff worth it for a public demo container.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize

# ── Stage 2: Frontend assets ─────────────────────────────────
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ── Stage 3: Runtime image ───────────────────────────────────
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libonig-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_sqlite mbstring exif pcntl bcmath gd zip intl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app ./
COPY --from=assets /app/public/build ./public/build

# Apache should serve the Laravel /public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

RUN mkdir -p storage/framework/{cache,cache/data,sessions,testing,views} storage/app/public storage/app/private storage/logs bootstrap/cache database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
