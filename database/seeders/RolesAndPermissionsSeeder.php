<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── PERMISOS ──────────────────────────────
        $permisos = [
            // Facturación
            'ver facturas', 'crear facturas', 'editar facturas', 'anular facturas',
            // Cotizaciones
            'ver cotizaciones', 'crear cotizaciones', 'editar cotizaciones',
            // Clientes
            'ver clientes', 'crear clientes', 'editar clientes', 'eliminar clientes',
            // Proveedores
            'ver proveedores', 'crear proveedores', 'editar proveedores',
            // Inventario
            'ver inventario', 'crear inventario', 'editar inventario',
            // Compras
            'ver compras', 'crear compras', 'aprobar compras',
            // Contable
            'ver recibos', 'crear recibos',
            // Reportes
            'ver reportes', 'exportar reportes',
            // Usuarios
            'ver usuarios', 'crear usuarios', 'editar usuarios', 'eliminar usuarios',
            // Configuración
            'ver configuracion', 'editar configuracion',
            // Nómina
            'ver nomina', 'gestionar nomina',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // ── ROLES (syncPermissions = idempotente, reemplaza siempre) ──────
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $vendedor   = Role::firstOrCreate(['name' => 'vendedor']);
        $bodeguero  = Role::firstOrCreate(['name' => 'bodeguero']);
        $contador   = Role::firstOrCreate(['name' => 'contador']);
        $lectura    = Role::firstOrCreate(['name' => 'solo-lectura']);

        // Super Admin — todo
        $superAdmin->syncPermissions(Permission::all());

        // Admin — casi todo menos eliminar usuarios
        $admin->syncPermissions([
            'ver facturas','crear facturas','editar facturas','anular facturas',
            'ver cotizaciones','crear cotizaciones','editar cotizaciones',
            'ver clientes','crear clientes','editar clientes',
            'ver proveedores','crear proveedores','editar proveedores',
            'ver inventario','crear inventario','editar inventario',
            'ver compras','crear compras','aprobar compras',
            'ver recibos','crear recibos',
            'ver reportes','exportar reportes',
            'ver usuarios','crear usuarios','editar usuarios',
            'ver configuracion',
            'ver nomina','gestionar nomina',
        ]);

        // Vendedor
        $vendedor->syncPermissions([
            'ver facturas','crear facturas',
            'ver cotizaciones','crear cotizaciones','editar cotizaciones',
            'ver clientes','crear clientes','editar clientes',
            'ver inventario',
            'ver reportes',
        ]);

        // Bodeguero
        $bodeguero->syncPermissions([
            'ver inventario','crear inventario','editar inventario',
            'ver compras','crear compras',
            'ver proveedores',
        ]);

        // Contador
        $contador->syncPermissions([
            'ver facturas','anular facturas',
            'ver recibos','crear recibos',
            'ver reportes','exportar reportes',
            'ver clientes','ver proveedores',
            'ver compras',
            'ver nomina','gestionar nomina',
        ]);

        // Solo lectura
        $lectura->syncPermissions([
            'ver facturas','ver cotizaciones','ver clientes',
            'ver proveedores','ver inventario','ver reportes',
        ]);

        // ── USUARIO SUPER ADMIN ───────────────────
        $usuario = User::firstOrCreate(
            ['email' => 'admin@facturaco.com'],
            [
                'name'         => 'Administrador',
                'password'     => Hash::make('Admin2026*'),
                'is_superadmin'=> true,
            ]
        );

        // Asegurar que siempre tenga is_superadmin=true (por si ya existía con false)
        $usuario->update(['is_superadmin' => true]);

        $usuario->syncRoles(['super-admin']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✅ Roles, permisos y usuario admin sincronizados correctamente.');
    }
}
