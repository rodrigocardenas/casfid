# ============================================
# PHP 8.2-FPM Backend (Laravel 11)
# ============================================
# Build stages multi-etapa para optimizar tamaño final

# Stage 1: Construcción de dependencias
FROM php:8.2-fpm-alpine AS builder

ARG PHP_VERSION=8.2

WORKDIR /var/www/html

# ============================================
# System Dependencies
# ============================================
RUN apk add --no-cache \
    build-base \
    libpq-dev \
    oniguruma-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    supervisor \
    bash

# ============================================
# PHP Extensions
# ============================================
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    opcache \
    zip \
    intl

# ============================================
# PHP Configuration
# ============================================
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# ============================================
# Composer
# ============================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# ============================================
# Install PHP Dependencies
# ============================================
COPY backend/composer.json backend/composer.lock* ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# ============================================
# Stage 2: Production Image
# ============================================
FROM php:8.2-fpm-alpine

ARG CONTAINER_UID=1000
ARG CONTAINER_GID=1000

WORKDIR /var/www/html

# ============================================
# System Dependencies (Runtime Only)
# ============================================
RUN apk add --no-cache \
    libpq \
    curl \
    git \
    bash \
    supervisor \
    postgresql-client

# ============================================
# Create Application User
# ============================================
RUN addgroup -g ${CONTAINER_GID} www-data && \
    adduser -D -G www-data -u ${CONTAINER_UID} www-data || true

# ============================================
# Copy PHP Extensions from Builder
# ============================================
COPY --from=builder /usr/local/etc/php /usr/local/etc/php
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php-fpm.d /usr/local/etc/php-fpm.d

# ============================================
# Copy Application Code from Builder
# ============================================
COPY --from=builder --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data backend/ .

# ============================================
# Create Required Directories
# ============================================
RUN mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap

# ============================================
# Health Check
# ============================================
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:9000/ping || exit 1

# ============================================
# Expose Port
# ============================================
EXPOSE 9000

# ============================================
# Switch to Application User
# ============================================
USER www-data

# ============================================
# Start PHP-FPM
# ============================================
CMD ["php-fpm"]
