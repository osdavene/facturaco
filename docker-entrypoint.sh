#!/bin/bash
set -e

cd /var/www/html

echo "=== Verificando variables ==="
echo "DB_HOST: ${DB_HOST}"
echo "DB_DATABASE: ${DB_DATABASE}"

# Escribir el .env en runtime con las variables de Railway
cat > .env << EOF
APP_NAME="${APP_NAME:-FacturaCO}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

DB_CONNECTION=pgsql
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE}"
DB_USERNAME="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
EOF

echo "=== .env generado ==="
cat .env | grep DB_HOST

# Ejecutar migraciones
php artisan migrate --force

# Crear enlace de storage
php artisan storage:link || true

# Iniciar Apache
exec apache2-foreground