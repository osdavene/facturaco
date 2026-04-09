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
            // Reportes
            'ver reportes', 'exportar reportes',
            // Usuarios
            'ver usuarios', 'crear usuarios', 'editar usuarios', 'eliminar usuarios',
            // Configuración
            'ver configuracion', 'editar configuracion',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // ── ROLES ─────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $vendedor   = Role::firstOrCreate(['name' => 'vendedor']);
        $bodeguero  = Role::firstOrCreate(['name' => 'bodeguero']);
        $contador   = Role::firstOrCreate(['name' => 'contador']);
        $lectura    = Role::firstOrCreate(['name' => 'solo-lectura']);

        // Super Admin — todo
        $superAdmin->givePermissionTo(Permission::all());

        // Admin — casi todo menos eliminar usuarios
        $admin->givePermissionTo([
            'ver facturas','crear facturas','editar facturas','anular facturas',
            'ver cotizaciones','crear cotizaciones','editar cotizaciones',
            'ver clientes','crear clientes','editar clientes',
            'ver proveedores','crear proveedores','editar proveedores',
            'ver inventario','crear inventario','editar inventario',
            'ver compras','crear compras','aprobar compras',
            'ver reportes','exportar reportes',
            'ver usuarios','crear usuarios','editar usuarios',
            'ver configuracion',
        ]);

        // Vendedor
        $vendedor->givePermissionTo([
            'ver facturas','crear facturas',
            'ver cotizaciones','crear cotizaciones','editar cotizaciones',
            'ver clientes','crear clientes','editar clientes',
            'ver inventario',
            'ver reportes',
        ]);

        // Bodeguero
        $bodeguero->givePermissionTo([
            'ver inventario','crear inventario','editar inventario',
            'ver compras','crear compras',
            'ver proveedores',
        ]);

        // Contador
        $contador->givePermissionTo([
            'ver facturas','anular facturas',
            'ver reportes','exportar reportes',
            'ver clientes','ver proveedores',
            'ver compras',
        ]);

        // Solo lectura
        $lectura->givePermissionTo([
            'ver facturas','ver cotizaciones','ver clientes',
            'ver proveedores','ver inventario','ver reportes',
        ]);

        // ── USUARIO SUPER ADMIN ───────────────────
        $usuario = User::firstOrCreate(
            ['email' => 'admin@facturaco.com'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('Admin2026*'),
            ]
        );

        $usuario->assignRole('super-admin');

        $this->command->info('✅ Roles, permisos y usuario admin creados correctamente.');
    }
}