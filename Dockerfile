FROM php:8.3-fpm

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
    && apt-get clean

# Instalar Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Instalar dependencias PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Instalar dependencias Node y compilar assets
RUN npm install && npm run build
RUN cp public/build/.vite/manifest.json public/build/manifest.json

# Verificar manifest
RUN ls public/build/.vite/manifest.json && echo "✓ Manifest encontrado"

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
CMD ["docker-entrypoint.sh"]