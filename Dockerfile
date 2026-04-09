FROM php:8.3-apache
# REBUILD-v7

RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev libpng-dev libxml2-dev libonig-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd mbstring xml bcmath opcache \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Fix Apache MPM conflict
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

# Configurar DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory ${APACHE_DOCUMENT_ROOT}>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

RUN echo "=== Assets ===" && ls public/build/ && cat public/build/manifest.json

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker-entrypoint-apache.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]