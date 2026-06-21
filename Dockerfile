FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libxml2-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring xml bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for layer caching
COPY composer.json ./

# Install PHP dependencies
RUN composer config --global policy.advisories.block false && \
    composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

# Copy the rest of the application
COPY . .

# Create required Laravel directories
RUN mkdir -p bootstrap/cache storage/logs storage/framework/cache \
             storage/framework/sessions storage/framework/views && \
    chmod -R 775 bootstrap/cache storage

# Generate optimised autoloader with the full app present
RUN composer dump-autoload --no-dev --optimize

EXPOSE 8080

CMD ["sh", "-c", "php artisan migrate --force && (php artisan db:seed --force || true) && exec php -S 0.0.0.0:${PORT:-8080} -t /app/public /app/server.php"]
