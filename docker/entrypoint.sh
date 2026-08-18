#!/bin/sh
set -e

# Render injects $PORT (defaults to 10000 if not set) — Apache must listen on it.
PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

# Render exposes the service's public URL as $RENDER_EXTERNAL_URL — use it as
# APP_URL if one wasn't set explicitly, so generated links/assets are correct.
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Generate an app key if one wasn't provided via environment variables.
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

# Make sure the SQLite file exists and is writable.
touch database/database.sqlite
chown www-data:www-data database/database.sqlite
chmod -R 775 storage bootstrap/cache

# DEMO MODE: reset the database to clean seed data on every boot.
# Render's free tier wipes the filesystem on every spin-down/restart anyway,
# so this keeps the public demo always in a known-good state.
php artisan migrate:fresh --seed --force

php artisan storage:link || true

exec "$@"
