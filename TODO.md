# TODO: Refactorización Controladores Grandes

Estado: Pendiente ✅

## Plan Aprobado
Refactorizar controladores grandes extrayendo a Actions/Services. Prioridad: ReporteController → ProductoController → Otros.

## Pasos Lógicos

### 1. Crear Services/Actions Base ✅
- ✅ `app/Services/ReporteService.php` (KPIs, queries ventas/inventario/cartera)
- ✅ `app/Actions/GenerarReportePdfAction.php`
- ✅ `app/Actions/GenerarReporteExcelAction.php`

### 2. Refactorizar ReporteController ✅
- ✅ Leer actual
- ✅ Mover lógica index() a ReporteService::kpisGenerales()
- ✅ Delegar PDFs/Excel a Actions
- [ ] Testear reportes

### 3. Crear Actions para ProductoController ✅
- ✅ `app/Actions/ActualizarProveedoresProductoAction.php`
- ✅ `app/Actions/AjustarStockProductoAction.php`

### 4. Refactorizar ProductoController ✅
- ✅ Delegar syncProveedores y ajustarStock a Actions
- [ ] Testear CRUD productos/stock

### 5. RemisionController & CotizacionController ✅
- ✅ CrearRemisionAction, ConvertirRemisionAFacturaAction
- ✅ CrearCotizacionAction, ConvertirCotizacionAFacturaAction
- ✅ Refactorizar ambos

### 6. BackupController & Backoffice (Pendiente)
- [ ] GenerarBackupAction
- [ ] Refactorizar

### 7. Verificación Final (Pendiente)
- [ ] php artisan test
- [ ] Manual: CRUDs, reportes, backups
- [ ] attempt_completion

Progreso: 2/7 completado
