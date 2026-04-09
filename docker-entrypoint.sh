#!/bin/bash
set -e

cd /var/www/html

echo "=== DATABASE_URL recibida: ${DATABASE_URL}"

# Generar .env completo en runtime
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

echo "=== .env generado, iniciando migraciones ==="

# Ejecutar migraciones
php artisan migrate --force

# Crear enlace de storage
php artisan storage:link || true

# Iniciar Apache
exec apache2-foreground