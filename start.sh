#!/bin/bash
set -e
cd /var/www/html

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

# Configurar Nginx en puerto 8080 con TCP (más confiable que socket)
cat > /etc/nginx/sites-available/default << 'NGINX'
server {
    listen 8080;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 300;
        include fastcgi_params;
    }

    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX

php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Iniciar PHP-FPM en background y esperar que arranque
php-fpm -D
sleep 3

echo "=== FacturaCO corriendo en puerto 8080 ==="
exec nginx -g 'daemon off;'