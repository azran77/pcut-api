web: php -S 0.0.0.0:$PORT -t /app/public
release: php artisan migrate --force && php artisan db:seed --force || true
