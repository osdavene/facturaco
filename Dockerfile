FROM php:8.3-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    curl \
    git \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        mbstring \
        xml \
        bcmath \
        opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache — deshabilitar mpm_event y habilitar mpm_prefork
RUN a2dismod mpm_event || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

# Configurar DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

# Instalar dependencias PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Compilar assets
RUN npm ci && npm run build

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
CMD ["docker-entrypoint.sh"]