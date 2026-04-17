<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Sincroniza permisos nuevos (ver nomina, ver recibos, etc.) a roles existentes.
 * Se ejecuta automáticamente en Railway con `php artisan migrate`.
 */
return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos nuevos si aún no existen
        $nuevos = ['ver nomina', 'gestionar nomina', 'ver recibos', 'crear recibos'];

        foreach ($nuevos as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        // Mapa: rol → permisos a garantizar (additive, no quita los existentes)
        $mapa = [
            'propietario' => $nuevos,
            'admin'       => $nuevos,
            'contador'    => ['ver nomina', 'gestionar nomina', 'ver recibos', 'crear recibos'],
        ];

        foreach ($mapa as $rolNombre => $permisos) {
            $rol = Role::where('name', $rolNombre)->first();
            if (!$rol) continue;

            foreach ($permisos as $p) {
                $permiso = Permission::where('name', $p)->where('guard_name', 'web')->first();
                if ($permiso && !$rol->hasPermissionTo($permiso)) {
                    $rol->givePermissionTo($permiso);
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Sin rollback — quitar permisos rompería usuarios existentes
    }
};
