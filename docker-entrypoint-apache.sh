#!/bin/bash
set -e

cd /var/www/html

echo "=== FacturaCO iniciando con Apache ==="

# Generar .env
cat > .env << EOF
APP_NAME="${APP_NAME:-FacturaCO}"
APP_ENV=production
APP_KEY="${APP_KEY}"
APP_DEBUG=false
APP_URL="${APP_URL:-https://facturaco-production.up.railway.app}"
APP_TIMEZONE=America/Bogota
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

DB_CONNECTION=pgsql
DATABASE_URL="${DATABASE_URL}"

CACHE_DRIVER=file
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

LOG_CHANNEL=stderr
LOG_LEVEL=error

MAIL_MAILER=log
MAIL_FROM_ADDRESS="facturacion@mundovirtual.co"
MAIL_FROM_NAME="FacturaCO"

VITE_APP_NAME="FacturaCO"
EOF

echo "=== .env generado ==="

# Configurar puerto de Apache (Railway usa PORT env var)
APACHE_PORT="${PORT:-80}"
echo "Listen ${APACHE_PORT}" > /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APACHE_PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "=== Apache en puerto ${APACHE_PORT} ==="

# Migraciones
php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo "=== Iniciando Apache ==="
exec apache2-foreground