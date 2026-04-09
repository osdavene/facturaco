FROM php:8.3-fpm
# REBUILD-v8-nginx-final

RUN apt-get update && apt-get install -y \
    nginx libpq-dev libzip-dev libpng-dev libxml2-dev libonig-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd mbstring xml bcmath opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

RUN ls public/build/ && cat public/build/manifest.json

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]