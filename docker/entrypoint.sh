#!/bin/sh
set -e

cd /app

# Ensure data dirs exist
mkdir -p /data/storage /data/storage/galleries
chown -R www-data:www-data /data || true

# Default local-disk storage path inside the volume.
# Laravel writes to storage_path('app') by default; we symlink it into /data.
if [ ! -L /app/storage/app ]; then
    rm -rf /app/storage/app
    mkdir -p /data/storage/app
    ln -s /data/storage/app /app/storage/app
fi

# Generate APP_KEY if missing
if [ -z "${APP_KEY:-}" ] && ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    if [ ! -f .env ]; then
        cp .env.example .env || true
    fi
    php artisan key:generate --force
fi

# SQLite DB
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/data/database.sqlite}"
    if [ ! -f "$DB_PATH" ]; then
        touch "$DB_PATH"
        chown www-data:www-data "$DB_PATH"
    fi
fi

# Migrate
php artisan migrate --force --no-interaction || true

# Cache (intentionally skip config:cache so env vars like APP_URL,
# FILESYSTEM_DISK, AWS_*, etc. are read fresh on every request).
php artisan config:clear --no-interaction || true
php artisan route:cache --no-interaction || true
php artisan view:cache --no-interaction || true

# Storage symlink (so /storage works if anyone uses it)
php artisan storage:link --no-interaction --quiet || true

chown -R www-data:www-data /app/storage /app/bootstrap/cache /data

exec "$@"
