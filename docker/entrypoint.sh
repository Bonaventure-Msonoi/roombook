#!/bin/sh
set -e

if [ -f artisan ]; then
    php artisan config:cache --no-interaction || true
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
    php artisan migrate --force --no-interaction || true
fi

exec "$@"
