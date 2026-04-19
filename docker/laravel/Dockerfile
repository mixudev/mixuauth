# ============================================================
# Dockerfile untuk Laravel 11 — Production-ready
# Build context: root direktori (AI-AUTH-02/)
# ============================================================

FROM php:8.4-fpm-alpine AS base

# -- Install PHP Extension Installer (Standard terbaik untuk Docker PHP)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# -- Instalasi ekstensi PHP yang diperlukan
# Script ini otomatis menangani library (libpng, libzip, dll) dan pembersihan
# redis di-pin ke versi spesifik agar diunduh dari GitHub (bukan PECL yang sering unreachable)
RUN install-php-extensions \
    pdo_mysql \
    zip \
    gd \
    intl \
    opcache \
    redis-6.1.0

# -- Install Composer ke base image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -- Konfigurasi PHP untuk production
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# ============================================================
# Stage: Composer dependencies
# ============================================================
FROM composer:2 AS deps

WORKDIR /app

COPY laravel-auth-ai/composer.json laravel-auth-ai/composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts

# ============================================================
# Stage: Final production image
# ============================================================
FROM base AS production

WORKDIR /var/www/html

COPY laravel-auth-ai/ .

COPY --from=deps /app/vendor ./vendor

# -- Permission
RUN chown -R www-data:www-data storage bootstrap/cache vendor \
    && chmod -R 775 storage bootstrap/cache vendor

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
