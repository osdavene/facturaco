#!/bin/bash
set -e

cd /var/www/html

APP_PORT="8080"

cat > /etc/nginx/sites-available/default << NGINX
server {
    listen ${APP_PORT};
    root /var/www/html/public;
    index index.php;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGINX

cat > .env << EOF
APP_NAME="${APP_NAME:-FacturaCO}"
APP_ENV=production
APP_KEY="${APP_KEY}"
APP_DEBUG=false
APP_URL="${APP_URL:-https://facturaco-production.up.railway.app}"
APP_TIMEZONE=America/Bogota
APP_LOCALE=es

DB_CONNECTION=pgsql
DATABASE_URL="${DATABASE_URL}"

CACHE_DRIVER=file
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
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

php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php-fpm -D
exec nginx -g 'daemon off;'