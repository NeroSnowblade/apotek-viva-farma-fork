#!/usr/bin/env bash
set -e

# Start script for Railway: ensure storage link, run migrations (optional), then start PHP built-in server

cd /var/www/html

# Create storage link if not exists
if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

# (Optional) Run migrations if RAILWAY_RUN_MIGRATIONS is set
if [ "${RAILWAY_RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force || true
fi

# Use PORT provided by Railway (or default to 8000)
PORT=${PORT:-8000}

echo "Starting PHP built-in server on 0.0.0.0:${PORT}"

php artisan serve --host=0.0.0.0 --port=${PORT}
