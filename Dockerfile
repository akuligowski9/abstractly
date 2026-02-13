# ============================================================
# Stage 1: Composer — install PHP dependencies
# ============================================================
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

# --ignore-platform-req=php+ ignores PHP upper-bound constraints
# (composer:2 ships PHP 8.5, but lockfile deps cap at 8.4;
#  the final runtime stage uses php:8.4-cli where they're compatible)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-req=php+

# ============================================================
# Stage 2: Node — build frontend assets (Vite + Tailwind v4)
# ============================================================
FROM node:20-alpine AS node

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

# Tailwind v4 @source directives in resources/css/app.css scan
# vendor blade files, so we need vendor/ available during build
COPY --from=composer /app/vendor ./vendor

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ============================================================
# Stage 3: PHP — final runtime image
# ============================================================
FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# (curl, mbstring, xml, simplexml, fileinfo are already in the base image)
RUN docker-php-ext-install \
    bcmath \
    opcache \
    pcntl \
    zip

WORKDIR /var/www/html

# Copy application source
COPY . .

# Copy PHP dependencies from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=node /app/public/build ./public/build

# Ensure storage directories exist with correct permissions
RUN mkdir -p \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Regenerate package manifest for production-only dependencies
# (local bootstrap/cache/*.php is excluded via .dockerignore because
#  it references dev packages like Dusk that aren't in --no-dev vendor)
RUN php artisan package:discover --ansi

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
