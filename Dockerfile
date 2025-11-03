### Multi-stage Dockerfile (single clean copy)

# Stage 1: Build frontend assets with Node
FROM node:18-alpine AS node-build
WORKDIR /app

# Copy package files and vite config, install and build
COPY package*.json vite.config.js ./
COPY resources resources
RUN npm ci
RUN npm run build

# Stage 2: PHP runtime with Apache
FROM php:8.2-apache

# System deps and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable apache mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy app source
COPY . /var/www/html

# Copy built assets from node stage into public
COPY --from=node-build /app/public /var/www/html/public

# Install PHP deps (including dev for demo projects)
RUN composer install --optimize-autoloader --no-interaction || true

# Permissions (safe for demo)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 80
CMD ["apache2-foreground"]
