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
ENV COMPOSER_NO_AUDIT=1
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

# Copy the rest of the application
COPY . .

# Generate optimised autoloader with the full app present
RUN composer dump-autoload --no-dev --optimize

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
