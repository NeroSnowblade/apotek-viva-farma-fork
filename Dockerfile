### Multi-stage Dockerfile (builds frontend assets so runtime only runs php artisan serve)

# Stage 1: Build frontend assets with Node (use non-alpine image for compatibility)
FROM node:18 AS node-build
WORKDIR /app

# Optionally download prebuilt frontend via ARTIFACT_URL, otherwise build
ARG ARTIFACT_URL
# Copy package files and lockfile if present, then install and build
COPY package.json package-lock.json* vite.config.js postcss.config.js tailwind.config.js ./
COPY resources resources
RUN sh -lc '\
        if [ -n "${ARTIFACT_URL}" ]; then \
            echo "Downloading prebuilt frontend from ${ARTIFACT_URL}"; \
            curl -fSL "${ARTIFACT_URL}/public.tar.gz" -o /tmp/public.tar.gz && mkdir -p public && tar -xzf /tmp/public.tar.gz -C public; \
        else \
            npm ci --prefer-offline --no-audit --progress=false && npm run build; \
        fi'

# Stage 2: PHP runtime (Apache)
FROM php:8.2-apache

# Install system deps and PHP extensions required by Laravel (including SQLite)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    curl \
    procps \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip || true \
    && rm -rf /var/lib/apt/lists/*

# Composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first to leverage layer caching. We DO NOT run composer
# or download vendor here; installation will be performed manually after deploy
# (set AUTO_RESTORE_VENDOR=true to enable automatic restore during container start).
COPY composer.json composer.lock* /var/www/html/

# Copy application source
COPY . /var/www/html

# Copy built frontend from node stage into public
COPY --from=node-build /app/public /var/www/html/public

# Ensure storage and cache directories exist and are writable
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Configure Apache document root to Laravel `public` and enable useful modules
RUN a2enmod rewrite headers || true
RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' /etc/apache2/sites-available/000-default.conf || true
RUN sed -ri 's!<Directory /var/www/>!<Directory /var/www/html/public>!g' /etc/apache2/apache2.conf || true

# Expose HTTP port
EXPOSE 80

# Copy start script and make executable
COPY scripts/start-railway.sh /var/www/html/scripts/start-railway.sh
RUN chmod +x /var/www/html/scripts/start-railway.sh || true

# Default command: run our start script which will run migrations (optional) then start Apache
CMD ["sh", "/var/www/html/scripts/start-railway.sh"]
