#!/bin/sh
# Starts the demo: a fresh database with the seeded data, then the web server.
# Nothing here survives a restart, which is the point: the demo always opens clean.
set -e

PORT="${PORT:-10000}"

# The public address the host gives the service (Render sets RENDER_EXTERNAL_URL).
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:$PORT}}"

# A key for this run. Sessions do not need to outlive a restart, the data does not either.
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY=" > .env
    php artisan key:generate --force
fi

rm -f "$DB_DATABASE"
touch "$DB_DATABASE"
php artisan migrate --force --seed

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="$PORT"
