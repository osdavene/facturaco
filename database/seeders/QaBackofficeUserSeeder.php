<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class QaBackofficeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'qa.backoffice@facturaco.local'],
            [
                'name' => 'QA Backoffice',
                'password' => Hash::make('Qa123456!'),
                'activo' => true,
                'is_superadmin' => true,
            ]
        );

        $role = Role::firstOrCreate(['name' => 'superadmin']);
        if (! $user->hasRole('superadmin')) {
            $user->assignRole($role);
        }

        $empresa = Empresa::first();
        if ($empresa && ! $user->empresas()->where('empresa_id', $empresa->id)->exists()) {
            $user->empresas()->attach($empresa->id, [
                'rol' => 'admin',
                'activo' => true,
            ]);
        }
    }
}
