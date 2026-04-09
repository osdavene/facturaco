#!/bin/bash
set -e

cd /var/www/html

# Limpiar cache de configuración
php artisan config:clear
php artisan cache:clear

# Ejecutar migraciones
php artisan migrate --force

# Crear enlace de storage
php artisan storage:link || true

# Iniciar Apache
apache2-foreground