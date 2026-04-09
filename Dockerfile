FROM php:8.3-fpm
# BUILD_FORCE_v5

RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    curl \
    git \
    unzip \
    && docker-php-ext-install \
        pdo pdo_pgsql pgsql zip gd mbstring xml bcmath opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

RUN echo "=== Verificando assets ===" && \
    ls public/build/ && \
    cat public/build/manifest.json

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
CMD ["docker-entrypoint.sh"]