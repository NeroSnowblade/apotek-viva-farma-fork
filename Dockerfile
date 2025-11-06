### Multi-stage Dockerfile (builds frontend assets so runtime only runs php artisan serve)

# Stage 1: Build frontend assets with Node (use non-alpine image for compatibility)
FROM node:18 AS node-build
WORKDIR /app

# Copy package files and lockfile if present, then install and build
COPY package.json package-lock.json* vite.config.js postcss.config.js tailwind.config.js ./
COPY resources resources
RUN npm ci --prefer-offline --no-audit --progress=false
RUN npm run build

# Stage 2: PHP runtime (CLI) - runs `php artisan serve` as entrypoint
FROM php:8.2-cli

# Install system deps and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    procps \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first to leverage layer caching, install deps
COPY composer.json composer.lock* /var/www/html/
RUN composer install --no-interaction --prefer-dist --optimize-autoloader || true

# Copy application source
COPY . /var/www/html

# Copy built frontend from node stage into public
COPY --from=node-build /app/public /var/www/html/public

# Ensure storage and cache directories exist and are writable
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Expose the default PHP built-in server port (Railway will provide $PORT at runtime)
EXPOSE 8000

# Copy start script and make executable
COPY scripts/start-railway.sh /var/www/html/scripts/start-railway.sh
RUN chmod +x /var/www/html/scripts/start-railway.sh || true

# Start app with php artisan serve (start script uses $PORT if provided)
CMD ["bash", "/var/www/html/scripts/start-railway.sh"]
