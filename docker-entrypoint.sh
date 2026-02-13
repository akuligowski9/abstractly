#!/bin/sh
set -e

# Copy .env.example to .env if .env doesn't exist
if [ ! -f .env ]; then
    echo "No .env file found — copying from .env.example"
    cp .env.example .env
fi

# Generate APP_KEY if not already set in the .env file
if [ -z "$(grep '^APP_KEY=.\+' .env)" ]; then
    echo "APP_KEY is empty — generating"
    KEY=$(php artisan key:generate --show)
    sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|" .env
    echo "APP_KEY set to $KEY"
fi

# Cache configuration, routes, and views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
