#!/bin/bash
set -e

cd /var/www/html

APP_PORT="8080"

echo "=== Iniciando FacturaCO en puerto ${APP_PORT} ==="

# ── Subir límite de subida de archivos PHP ─────────────────────────────────
# Por defecto PHP sólo permite 2MB; lo subimos a 10MB para que la validación
# de Laravel pueda mostrar el error amigable en vez de la página de error 500.
PHP_INI_DIR=$(php --ini | grep "Loaded Configuration" | awk '{print $NF}' | xargs dirname 2>/dev/null || echo "/usr/local/etc/php")
mkdir -p "${PHP_INI_DIR}/conf.d"
cat > "${PHP_INI_DIR}/conf.d/99-facturaco-uploads.ini" << 'PHP_INI'
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 60
PHP_INI
echo "=== PHP upload_max_filesize seteado a 10M ==="

# Configurar Nginx
cat > /etc/nginx/sites-available/default << 'NGINX'
server {
    listen 8080;
    root /var/www/html/public;
    index index.php index.html;

    access_log /dev/stdout;
    error_log /dev/stderr;

    # Subir el límite también en Nginx (debe coincidir con PHP)
    client_max_body_size 10M;

    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTP_HOST $http_host;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX

# Generar .env con variables de Railway
cat > .env << EOF
APP_NAME="${APP_NAME:-FacturaCO}"
APP_ENV=production
APP_KEY="${APP_KEY}"
APP_DEBUG=false
APP_URL="${APP_URL:-https://facturaco-production.up.railway.app}"
APP_TIMEZONE=America/Bogota
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_CO
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
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="facturacion@mundovirtual.co"
MAIL_FROM_NAME="FacturaCO"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="FacturaCO"
EOF

echo "=== .env generado ==="

# Ejecutar migraciones (|| true para que un fallo no mate el servidor)
echo "=== Ejecutando migraciones ==="
php artisan migrate --force || echo "=== ADVERTENCIA: migración falló, el servidor igual arrancará ==="

# Storage link
php artisan storage:link 2>/dev/null || true

# Limpiar cache de vistas
php artisan view:clear 2>/dev/null || true

echo "=== Iniciando PHP-FPM ==="
php-fpm -D

echo "=== Nginx iniciando en puerto ${APP_PORT} ==="
exec nginx -g 'daemon off;'