#!/bin/bash
set -e

cd /var/www/html

# Generar .env con variables de Railway
cat > .env << EOF
APP_NAME="${APP_NAME:-FacturaCO}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

DB_CONNECTION=pgsql
DATABASE_URL="${DATABASE_URL}"

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
EOF

# Migraciones y storage
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

# Iniciar PHP-FPM en background
php-fpm -D

# Iniciar Nginx en foreground
exec nginx -g 'daemon off;'