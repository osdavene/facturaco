#!/bin/bash
# SIN set -e para que ningún fallo mate el arranque del servidor

cd /var/www/html

# Railway asigna $PORT dinámicamente; si no está definido usamos 8080
APP_PORT="${PORT:-8080}"

echo "=== Iniciando FacturaCO en puerto ${APP_PORT} ==="

# ── PHP ini ────────────────────────────────────────────────────────────────
PHP_INI_DIR=$(php --ini 2>/dev/null | grep "Loaded Configuration" | awk '{print $NF}' | xargs dirname 2>/dev/null || echo "/usr/local/etc/php")
mkdir -p "${PHP_INI_DIR}/conf.d"
cat > "${PHP_INI_DIR}/conf.d/99-facturaco-uploads.ini" << 'PHP_INI'
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 60
PHP_INI
echo "=== PHP ini configurado ==="

# ── Nginx ──────────────────────────────────────────────────────────────────
# Usamos ${APP_PORT} y escapamos las variables propias de nginx con \$
cat > /etc/nginx/sites-available/default << NGINX_CONF
server {
    listen ${APP_PORT};
    root /var/www/html/public;
    index index.php index.html;

    access_log /dev/stdout;
    error_log /dev/stderr;

    client_max_body_size 10M;

    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTP_HOST \$http_host;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX_CONF

echo "=== Nginx configurado en puerto ${APP_PORT} ==="

# ── .env ───────────────────────────────────────────────────────────────────
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

# ── Fix empresa_id directamente con PDO (sin depender de migraciones) ──────
echo "=== Verificando columnas empresa_id ==="
php -r "
try {
    \$url = getenv('DATABASE_URL');
    \$pdo = new PDO(\$url, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    \$empresa_id = \$pdo->query('SELECT id FROM empresa ORDER BY id LIMIT 1')->fetchColumn();
    if (!\$empresa_id) \$empresa_id = 1;

    \$tablas = ['clientes','proveedores','productos','categorias','unidades_medida',
               'facturas','cotizaciones','remisiones','ordenes_compra',
               'recibos_caja','notas_credito','movimientos_inventario'];

    foreach (\$tablas as \$t) {
        \$existe = \$pdo->query(\"SELECT COUNT(*) FROM information_schema.columns WHERE table_name='\$t' AND column_name='empresa_id'\")->fetchColumn();
        if (!\$existe) {
            \$pdo->exec(\"ALTER TABLE \\\"\$t\\\" ADD COLUMN empresa_id BIGINT NOT NULL DEFAULT \$empresa_id REFERENCES empresa(id) ON DELETE CASCADE\");
            \$pdo->exec(\"ALTER TABLE \\\"\$t\\\" ALTER COLUMN empresa_id DROP DEFAULT\");
            echo \"  empresa_id agregado a: \$t\n\";
        } else {
            echo \"  empresa_id ya existe en: \$t\n\";
        }
    }
    echo 'OK\n';
} catch (Exception \$e) {
    echo 'ERROR fix empresa_id: ' . \$e->getMessage() . \"\n\";
}
" 2>&1 || echo "=== Script empresa_id falló ==="

# ── Preparación rápida ──────────────────────────────────────────────────────
php artisan storage:link 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# ── PHP-FPM ────────────────────────────────────────────────────────────────
echo "=== Iniciando PHP-FPM ==="
php-fpm -D
echo "=== PHP-FPM iniciado (PID: $!) ==="

# ── Migraciones en background (no bloquean el arranque) ───────────────────
echo "=== Migraciones iniciando en background ==="
(php artisan migrate --force 2>&1 && echo "=== Migraciones completadas ===") \
    || echo "=== ADVERTENCIA: migración falló ===" &

# ── Nginx en primer plano (mantiene el container vivo) ────────────────────
echo "=== Nginx iniciando ==="
exec nginx -g 'daemon off;'
