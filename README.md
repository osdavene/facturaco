# FacturaCO

Sistema de gestión empresarial colombiano. Facturación, inventario, contabilidad, nómina y pagos en línea. Multi-empresa con aislamiento por tenant.

**Stack:** Laravel 13 · PHP 8.3 · PostgreSQL · Tailwind CSS · Alpine.js · Railway

---

## Módulos

| Módulo | Descripción |
|---|---|
| Facturación | Facturas, cotizaciones, remisiones, notas crédito |
| Recibos de Caja | Registro de pagos recibidos |
| Clientes / Proveedores | Terceros con tipo de persona, régimen, retenciones |
| Inventario | Productos, categorías, unidades, movimientos con kardex |
| Órdenes de Compra | Solicitudes a proveedores |
| Contabilidad | Plan de cuentas, asientos automáticos por documento |
| Nómina | Empleados, liquidación mensual, colilla de pago |
| Reportes | Ventas, cartera, inventario, impuestos |
| Pagos en línea | Integración Wompi (PSE, Nequi, tarjeta) |
| Usuarios y Roles | Spatie Permission · propietario / admin / operador |
| Multi-empresa | Grupos con matriz y filiales, contexto por sesión |
| Backoffice | Panel de superadmin para gestionar empresas y módulos |

---

## Arquitectura multi-empresa

Cada empresa tiene su propio contexto aislado:

- `empresa_id` en todas las tablas de negocio
- Contexto activo en sesión: `session('empresa_activa_id')`
- Trait `PertenecerEmpresa` → global scope automático + auto-fill en `create()`
- Middleware `EnsureEmpresaSeleccionada` → redirige si no hay empresa activa
- Grupos: una empresa puede ser matriz con N filiales

**Tablas con empresa_id:** clientes, proveedores, productos, categorias, unidades_medida, facturas, cotizaciones, remisiones, ordenes_compra, recibos_caja, notas_credito, movimientos_inventario, empleados, nominas.

---

## Roles y permisos

Dos sistemas separados:

| Sistema | Campo/herramienta | Para qué |
|---|---|---|
| Superadmin plataforma | `users.is_superadmin = true` | Acceso solo al backoffice |
| Roles empresa | Spatie Permission | Control de acceso a módulos y acciones |

**Roles Spatie:** `propietario` (acceso total a su empresa) · `admin` · `operador`

`propietario` tiene un `Gate::before` que bypasea todos los permisos dentro de su empresa.

---

## Instalación local

### Requisitos

- PHP 8.3+
- PostgreSQL 15+
- Node.js 20+
- Composer 2.x

### Pasos

```bash
git clone <repo> facturaco
cd facturaco

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus datos de PostgreSQL, luego:

```bash
php artisan migrate
php artisan db:seed --class=ModuloSeeder
php artisan db:seed --class=ColombiaDivisionSeeder
```

Crear el primer superadmin de plataforma:

```bash
php artisan admin:superadmin
```

Crear el primer usuario de empresa:

```bash
php artisan admin:crear --empresa=1 --rol=propietario
```

Levantar el servidor:

```bash
php artisan serve
php artisan queue:work   # en otra terminal
```

---

## Variables de entorno clave

```env
# Base de datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=facturaco
DB_USERNAME=postgres
DB_PASSWORD=

# Cola (database en producción, sync en local)
QUEUE_CONNECTION=database

# Correo del sistema (notificaciones internas)
MAIL_MAILER=log        # cambiar a smtp en producción
MAIL_FROM_ADDRESS=facturacion@tudominio.co

# Wompi (pagos en línea - opcional)
WOMPI_PUBLIC_KEY=
WOMPI_EVENTS_SECRET=

# App URL
APP_URL=https://tudominio.co
```

El correo SMTP **por empresa** se configura desde la interfaz en Empresa → Configuración de Correo, no desde `.env`.

---

## Colas (Queue)

Los emails de facturas se procesan en cola para no bloquear la respuesta HTTP.

```bash
# Desarrollo
php artisan queue:work --sleep=3 --tries=3

# Ver jobs fallidos
php artisan queue:failed

# Reintentar un job fallido
php artisan queue:retry <id>

# Limpiar jobs fallidos
php artisan queue:flush
```

En Railway el worker se inicia automáticamente desde `start.sh` con reinicio automático cada 500 jobs.

---

## Scheduler

Las alertas automáticas (facturas vencidas, stock bajo, cotizaciones por vencer) se envían vía scheduler.

```bash
# Agregar al crontab del servidor
* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

En Railway configurar un cron job que ejecute `php artisan schedule:run`.

Comandos disponibles:

```bash
php artisan alertas:enviar          # envía notificaciones pendientes
php artisan sistema:verificar       # diagnóstico del sistema
php artisan admin:crear             # crea usuario de empresa interactivo
php artisan admin:superadmin        # crea superadmin de plataforma interactivo
```

---

## Despliegue en Railway

El proyecto usa Docker implícito de Railway con `start.sh` como entrypoint.

### Variables de entorno requeridas en Railway

```
APP_KEY=base64:...
APP_URL=https://tuapp.up.railway.app
DATABASE_URL=postgresql://...     # Railway lo inyecta automáticamente
```

### Lo que hace `start.sh` en cada deploy

1. Configura PHP ini (upload limits, memory)
2. Configura Nginx con el puerto dinámico de Railway
3. Genera `.env` desde variables de entorno de Railway
4. Ejecuta `php artisan migrate --force`
5. Ejecuta `ModuloSeeder` y `ColombiaDivisionSeeder`
6. Inicia PHP-FPM
7. Inicia queue worker con reinicio automático cada 500 jobs
8. Inicia Nginx en primer plano

---

## Geografía Colombia (DIVIPOLA)

Las tablas `departamentos` y `municipios` contienen los 33 departamentos + "OTRO" y 1.116 municipios según códigos DANE. Se cargan con:

```bash
php artisan db:seed --class=ColombiaDivisionSeeder
```

El seeder es idempotente — puede ejecutarse múltiples veces sin duplicar datos.

---

## Correo por empresa

Cada empresa configura su propio SMTP desde **Empresa → Configuración de Correo**.

**Campos:** host · puerto · cifrado · usuario · contraseña · remitente

**Proveedores con autoconfiguración:** Resend · Gmail · Outlook · Yahoo · Mailgun · SendGrid

El sistema crea un mailer Laravel nombrado por empresa (`empresa_smtp_{id}`) para aislar configuraciones entre tenants. Los emails de facturas se procesan en cola con 3 reintentos.

---

## Flujo de facturación

```
borrador → emitida → pagada
                  ↘ vencida  (scheduler automático)
```

- Consecutivos por empresa con prefijo configurable (FE, FC, etc.)
- PDF generado con DomPDF + código QR con datos del documento
- Envío por email con PDF adjunto (procesado en cola)
- Integración Wompi: botón de pago en línea en vista pública de la factura

### Documentos y sus efectos

| Documento | Afecta inventario | Afecta contabilidad |
|---|---|---|
| Factura | Sí (salida) | Sí (asiento automático) |
| Cotización | No | No |
| Remisión | Sí (salida) | No |
| Orden de Compra | Sí (entrada) | No |
| Nota Crédito | Sí (reversión) | Sí (reversión) |
| Recibo de Caja | No | Sí |

---

## Nómina

Constantes 2025:
- SMMLV: $1.423.500
- Auxilio de transporte: $202.050

Prestaciones: prima · cesantías · intereses cesantías · vacaciones

Seguridad social: salud (8,5%) · pensión (12%) · ARL según nivel de riesgo · ICBF · SENA · caja de compensación

---

## Estructura del proyecto

```
app/
├── Actions/           # Casos de uso desacoplados
├── Console/Commands/  # Comandos Artisan del sistema
├── Http/
│   ├── Controllers/   # Un controller por módulo
│   ├── Middleware/    # EnsureEmpresaSeleccionada, EnsureModuloActivo
│   └── Requests/      # Form Requests con validación por módulo
├── Jobs/              # EnviarFacturaJob (procesado en cola)
├── Mail/              # FacturaMail con PDF adjunto
├── Models/            # Eloquent con traits, scopes y casts
│   └── Traits/        # PertenecerEmpresa (multi-tenancy automático)
├── Notifications/     # Alertas automatizadas
├── Providers/         # AppServiceProvider (Gates y macros)
└── Services/          # MailService, PdfService, NominaService, etc.

routes/web/
├── facturacion.php    # Facturas, cotizaciones, remisiones, notas crédito
├── clientes.php
├── inventario.php
├── contabilidad.php
├── nomina.php
├── reportes.php
├── configuracion.php  # Empresa, usuarios, módulos
├── backoffice.php     # Panel superadmin de plataforma
├── api_interna.php    # Endpoints JSON (departamentos, municipios)
└── dashboard.php
```

---

## Seguridad

- Autenticación: Laravel Breeze
- Autorización: Spatie Laravel Permission + Gates personalizados
- Aislamiento entre empresas: middleware + global scopes por `empresa_id`
- Contraseñas SMTP/Wompi: en DB, excluidas de arrays con `$hidden`
- Rate limiting en rutas de autenticación
- CSRF en todos los formularios
- Soft deletes: clientes, proveedores y documentos se archivan, no se eliminan físicamente
