#!/bin/bash
set -e

cd /var/www/html

echo "=== Variables de entorno ==="
echo "DB_HOST: $DB_HOST"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"

# Limpiar todo el cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Ejecutar migraciones
php artisan migrate --force

# Crear enlace de storage
php artisan storage:link || true

# Iniciar Apache
apache2-foreground