#!/bin/bash
set -e

cd /var/www/html

# Generar .env completo con todas las variables de Railway
cat > .env << EOF
APP_NAME="${APP_NAME:-FacturaCO}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"
APP_LOCALE="${APP_LOCALE:-es}"
APP_FALLBACK_LOCALE="${APP_FALLBACK_LOCALE:-es}"

DB_CONNECTION=pgsql
DATABASE_URL="${DATABASE_URL}"

CACHE_DRIVER=file
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

LOG_CHANNEL=stderr
LOG_LEVEL=error

MAIL_MAILER="${MAIL_MAILER:-log}"
MAIL_HOST="${MAIL_HOST:-localhost}"
MAIL_PORT="${MAIL_PORT:-587}"
MAIL_USERNAME="${MAIL_USERNAME}"
MAIL_PASSWORD="${MAIL_PASSWORD}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-hello@example.com}"
MAIL_FROM_NAME="${APP_NAME:-FacturaCO}"

FILESYSTEM_DISK=local
EOF

# Migraciones y storage
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

# Iniciar PHP-FPM en background
php-fpm -D

# Iniciar Nginx en foreground
exec nginx -g 'daemon off;'