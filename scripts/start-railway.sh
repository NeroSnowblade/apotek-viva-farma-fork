#!/usr/bin/env sh
set -e

# Start script for Railway: ensure storage link, run migrations (optional), then start PHP built-in server

cd /var/www/html

# Create storage link if not exists
if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

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

# Use PORT provided by Railway (or default to 8000)
PORT=${PORT:-8000}
echo "PORT=${PORT} -- starting server"

# If Apache is available (php:apache image), prefer it. Otherwise fallback to php artisan serve.
if command -v apache2-foreground >/dev/null 2>&1; then
  echo "Starting Apache (apache2-foreground)..."
  # exec so PID 1 is apache and logs are attached to container
  exec apache2-foreground
else
  echo "Starting PHP built-in server on 0.0.0.0:${PORT}"
  exec php artisan serve --host=0.0.0.0 --port=${PORT}
fi
