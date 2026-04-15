# TODO - Modularización Fase 3 (Backoffice módulos por empresa)

- [x] Revisar `BackofficeController` y vistas actuales de empresas
- [x] Agregar métodos `modulos()` y `modulosUpdate()` en `BackofficeController`
- [x] Agregar rutas backoffice para gestionar módulos de empresa en `routes/web.php`
- [x] Crear vista `resources/views/backoffice/empresas/modulos.blade.php`
- [x] Integrar acceso a gestión de módulos desde listado/edición de empresas (si aplica)
- [x] Corregir despliegue Railway: ejecutar `ModuloSeeder` en arranque (`start.sh`) para evitar tabla `modulos` vacía
- [ ] Probar flujo: asignar/quitar módulos y persistencia en `empresa_modulo`
- [ ] Probar efecto funcional: acceso permitido/403 según módulo activo
- [ ] Validación post-deploy Railway:
  - [ ] Confirmar `modulos` con registros
  - [ ] Confirmar UI con checkboxes visibles
  - [ ] Confirmar guardado en `empresa_modulo`
