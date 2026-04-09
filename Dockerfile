FROM php:8.3-fpm
# BUILD_FORCE_v4

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

# Instalar dependencias PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Instalar dependencias Node y compilar assets
RUN npm install && npm run build

# Asegurar manifest.json en public/build/ (compatible con Vite 5 y Vite 6+)
RUN echo "=== Archivos en public/build/ ===" && ls -la public/build/ && \
    if [ -f "public/build/manifest.json" ]; then \
        echo "OK: manifest.json ya existe en public/build/"; \
    elif [ -f "public/build/.vite/manifest.json" ]; then \
        cp "public/build/.vite/manifest.json" "public/build/manifest.json" && \
        echo "OK: manifest.json copiado desde .vite/"; \
    else \
        echo "ERROR: No se encontro manifest.json" && exit 1; \
    fi && echo "=== Manifest OK ==="

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copiar script de inicio
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
CMD ["docker-entrypoint.sh"]