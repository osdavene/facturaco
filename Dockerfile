FROM php:8.3-fpm

# Instalar dependencias del sistema
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

# Instalar Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Instalar dependencias PHP (sin scripts para no necesitar .env)
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Instalar dependencias Node
RUN npm install

# Compilar assets con Vite
RUN npm run build

# Copiar manifest al lugar que Laravel espera
RUN cp public/build/.vite/manifest.json public/build/manifest.json && \
    echo "=== Manifest copiado correctamente ===" && \
    cat public/build/manifest.json

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copiar script de inicio
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
CMD ["docker-entrypoint.sh"]