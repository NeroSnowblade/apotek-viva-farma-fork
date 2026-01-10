#!/usr/bin/env sh
set -e

# Start script for Railway: ensure storage link, run migrations (optional), then start PHP built-in server

cd /var/www/html

# Create storage link if not exists
if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

# Ensure storage, cache and database directories exist and are writable by Apache (www-data)
mkdir -p storage/logs bootstrap/cache database || true
chown -R www-data:www-data storage bootstrap/cache database || true
chmod -R 775 storage bootstrap/cache database || true
# Ensure laravel log file exists
touch storage/logs/laravel.log || true
chown www-data:www-data storage/logs/laravel.log || true

# Ensure database directory and sqlite file exist (for SQLite setup)
if [ "${DB_CONNECTION}" = "sqlite" ]; then
  # ensure directory exists
  mkdir -p database
  if [ ! -f database/database.sqlite ]; then
    echo "Creating SQLite database file at database/database.sqlite"
    touch database/database.sqlite
    # try best-effort permission fix; harmless when it fails on some hosts
    chown -R www-data:www-data database || true
    chmod 664 database/database.sqlite || true
  fi

  # (Optional) Run migrations if RAILWAY_RUN_MIGRATIONS is set
  if [ "${RAILWAY_RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations (SQLite)..."
    php artisan migrate --force || true
  fi
fi

# Debug: list enabled Apache modules and config test to help diagnose MPM errors
if command -v apache2ctl >/dev/null 2>&1; then
  echo "--- Apache mods-enabled ---"
  ls -la /etc/apache2/mods-enabled || true
  echo "--- apache2ctl -M ---"
  apache2ctl -M || true
  echo "--- apache2ctl configtest ---"
  apache2ctl configtest || true
fi

# Ensure vendor dependencies exist; try ARTIFACT_URL then composer install
if [ ! -f vendor/autoload.php ]; then
  echo "vendor/autoload.php not found. Attempting to restore vendor..."
  if [ -n "${ARTIFACT_URL}" ]; then
    echo "ARTIFACT_URL is set, attempting download from ${ARTIFACT_URL}"
    echo "Downloading ${ARTIFACT_URL}/vendor.tar.gz to /tmp/vendor.tar.gz"
    if curl -fSL "${ARTIFACT_URL}/vendor.tar.gz" -o /tmp/vendor.tar.gz; then
      echo "Download OK. /tmp/vendor.tar.gz size: $(stat -c%s /tmp/vendor.tar.gz 2>/dev/null || echo 'unknown')"
      mkdir -p vendor
      if tar -tzf /tmp/vendor.tar.gz >/dev/null 2>&1; then
        tar -xzf /tmp/vendor.tar.gz -C ./
        echo "Extracted vendor from artifact. Contents:"
        ls -la vendor | sed -n '1,200p' || true
      else
        echo "Downloaded vendor.tar.gz is not a valid tar.gz" >&2
      fi
    else
      echo "Failed to download vendor.tar.gz from ${ARTIFACT_URL}" >&2
    fi
  fi

  if [ ! -f vendor/autoload.php ]; then
    echo "vendor still missing; attempting composer install (may take longer)..."
    if command -v composer >/dev/null 2>&1; then
      composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction || RC=$?
      RC=${RC:-0}
      echo "composer install exit code: $RC"
      if [ $RC -ne 0 ]; then
        echo "composer install failed. See composer output above." >&2
      else
        echo "composer install completed successfully"
      fi
    else
      echo "composer not found in container; skipping composer install." >&2
    fi
  fi

  echo "Vendor status after restore attempts:"
  ls -la vendor 2>/dev/null || echo "vendor directory not present"
  if [ ! -f vendor/autoload.php ]; then
    echo "FATAL: vendor/autoload.php still missing after attempts. Aborting startup." >&2
    # keep container alive for debugging: show /var/www/html listing and exit non-zero
    echo "--- /var/www/html listing ---"
    ls -la /var/www/html || true
    exit 1
  fi
fi

# Ensure all Laravel cache dirs exist and are writable
mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Ensure APP_KEY exists; generate if missing (best-effort)
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "" ]; then
  echo "APP_KEY missing; attempting to generate one..."
  if command -v php >/dev/null 2>&1 && [ -f artisan ]; then
    php artisan key:generate --ansi || true
  fi
fi

# Clear compiled caches so Laravel can write fresh cache files
if command -v php >/dev/null 2>&1 && [ -f artisan ]; then
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear || true
  php artisan cache:clear || true
fi

# Debug: show configured view compiled path and check it's writable
if command -v php >/dev/null 2>&1 && [ -f vendor/autoload.php ]; then
  echo "--- Laravel view.compiled (config) ---"
  php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; echo \$app->make('config')->get('view.compiled') . PHP_EOL;" || true
  VIEW_COMPILED_PATH=$(php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; echo \$app->make('config')->get('view.compiled');" 2>/dev/null || echo "")
  if [ -n "${VIEW_COMPILED_PATH}" ]; then
    echo "Checking path: ${VIEW_COMPILED_PATH}"
    ls -la "${VIEW_COMPILED_PATH%/*}" || true
    mkdir -p "${VIEW_COMPILED_PATH%/*}" || true
    chown -R www-data:www-data "${VIEW_COMPILED_PATH%/*}" || true
    chmod -R 775 "${VIEW_COMPILED_PATH%/*}" || true
    # try writing a temp file
    touch "${VIEW_COMPILED_PATH%/*}/.write_test" && echo "write ok" || echo "write failed"
    rm -f "${VIEW_COMPILED_PATH%/*}/.write_test" || true
  else
    echo "Could not determine view.compiled path from config." || true
  fi
fi

# (Optional) Run seeders if RAILWAY_RUN_SEEDS is set
if [ "${RAILWAY_RUN_SEEDS:-false}" = "true" ]; then
  SEED_CLASS=${RAILWAY_SEED_CLASS:-DatabaseSeeder}
  echo "Running database seeders (class=${SEED_CLASS})..."
  # Use --force so it runs in non-interactive containers
  php artisan db:seed --class=${SEED_CLASS} --force || true
fi

# Use PORT provided by Railway (or default to 8000)
PORT=${PORT:-8000}
echo "PORT=${PORT} -- starting server"

# Start PHP-FPM + nginx if available; otherwise fallback to php artisan serve
if command -v php-fpm >/dev/null 2>&1 && command -v nginx >/dev/null 2>&1; then
  echo "Starting php-fpm and nginx..."
  # ensure php-fpm is running (daemonize), then run nginx in foreground
  php-fpm -D || true
  exec nginx -g 'daemon off;'
else
  echo "Starting PHP built-in server on 0.0.0.0:${PORT}"
  exec php artisan serve --host=0.0.0.0 --port=${PORT}
fi
