#!/bin/sh
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force || echo "Seeding skipped (already seeded)"

echo "Starting server on port ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t /app/public
