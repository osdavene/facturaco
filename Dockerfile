FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    nginx libpq-dev libzip-dev libpng-dev libxml2-dev libonig-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd mbstring xml bcmath opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

RUN mkdir -p /var/www/html/storage/app/public \
             /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/testing \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]