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
FROM php:8.2-fpm

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

# Copy composer files first to leverage layer caching, install deps or download vendor
ARG ARTIFACT_URL
COPY composer.json composer.lock* /var/www/html/
RUN sh -lc '\
        if [ -n "${ARTIFACT_URL}" ]; then \
            echo "Downloading prebuilt vendor from ${ARTIFACT_URL}"; \
            curl -fSL "${ARTIFACT_URL}/vendor.tar.gz" -o /tmp/vendor.tar.gz && mkdir -p /var/www/html/vendor && tar -xzf /tmp/vendor.tar.gz -C /var/www/html/vendor; \
        else \
            composer install --no-interaction --prefer-dist --optimize-autoloader; \
        fi'

# Copy application source
COPY . /var/www/html

# Copy built frontend from node stage into public
COPY --from=node-build /app/public /var/www/html/public

# Ensure storage and cache directories exist and are writable
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Install nginx and necessary packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Configure nginx to serve Laravel from /var/www/html/public and pass PHP to php-fpm
RUN rm /etc/nginx/sites-enabled/default || true
RUN printf '%s\n' "server {" \
  "    listen 80;" \
  "    server_name _;" \
  "    root /var/www/html/public;" \
  "    index index.php index.html;" \
    "    location / { try_files \$uri /index.php?\$query_string; }" \
    "    location ~ \.php$ {" \
    "        include fastcgi_params;" \
    "        fastcgi_pass 127.0.0.1:9000;" \
    "        fastcgi_index index.php;" \
    "        fastcgi_param SCRIPT_FILENAME /var/www/html/public\$fastcgi_script_name;" \
    "    }" \
  "    location ~ /\.ht { deny all; }" \
  "}" > /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configure php-fpm to listen on TCP 127.0.0.1:9000 instead of unix socket
RUN if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then \
            sed -ri "s!^listen\s*=\s*.*$!listen = 127.0.0.1:9000!" /usr/local/etc/php-fpm.d/www.conf || true; \
        fi

# Expose HTTP port
EXPOSE 80

# Copy start script and make executable
COPY scripts/start-railway.sh /var/www/html/scripts/start-railway.sh
RUN chmod +x /var/www/html/scripts/start-railway.sh || true

# Default command: run our start script which will run migrations (optional) then start Apache
CMD ["sh", "/var/www/html/scripts/start-railway.sh"]
