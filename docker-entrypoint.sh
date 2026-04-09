#!/bin/bash
set -e

cd /var/www/html

# Railway siempre usa 8080
APP_PORT="8080"

echo "Configurando Nginx en puerto ${APP_PORT}"

# Generar configuracion de Nginx
cat > /etc/nginx/sites-available/default << NGINX
server {
    listen ${APP_PORT};
    root /var/www/html/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGINX

# Generar .env
cat > .env << EOF
APP_NAME="${APP_NAME:-FacturaCO}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"
APP_LOCALE="${APP_LOCALE:-es}"

DB_CONNECTION=pgsql
DATABASE_URL="${DATABASE_URL}"

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
LOG_LEVEL=error

MAIL_MAILER="${MAIL_MAILER:-log}"
FILESYSTEM_DISK=local
EOF

# Migraciones y storage
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

# PHP-FPM en background
php-fpm -D

echo "Nginx iniciando en puerto ${APP_PORT}..."
exec nginx -g 'daemon off;'