### Multi-stage Dockerfile (single clean copy)

# Stage 1: Build frontend assets with Node
FROM node:18-alpine AS node-build
WORKDIR /app

# Copy package files and vite config, install and build
COPY package*.json vite.config.js ./
COPY resources resources
RUN npm ci
RUN npm run build

# Stage 2: PHP runtime (CLI) - suitable for running `php artisan serve`
FROM php:8.2-cli

# System deps and PHP extensions
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

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app source
COPY . /var/www/html

# Copy built assets from node stage into public
COPY --from=node-build /app/public /var/www/html/public

# Install PHP deps (include dev deps for demo).
# Fail the build if composer install fails so we catch issues early.
RUN composer install --optimize-autoloader --no-interaction

# Permissions (safe for demo)
RUN chown -R 1000:1000 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Expose the port that php artisan serve will use (Railway provides $PORT at runtime)
EXPOSE 8000

# Copy start script and make executable
COPY scripts/start-railway.sh /var/www/html/scripts/start-railway.sh
RUN chmod +x /var/www/html/scripts/start-railway.sh || true

# Default command: run artisan serve so Railway can start the app using $PORT
CMD ["bash", "/var/www/html/scripts/start-railway.sh"]
