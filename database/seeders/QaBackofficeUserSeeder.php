<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QaBackofficeUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'qa.backoffice@facturaco.local'],
            [
                'name'         => 'QA BACKOFFICE',
                'password'     => Hash::make('Qa123456!'),
                'activo'       => true,
                'is_superadmin'=> true,
            ]
        );

        // Asegurar is_superadmin=true y sin roles Spatie.
        // El acceso al backoffice es solo por is_superadmin, no por roles de empresa.
        $user->update(['is_superadmin' => true]);
        $user->syncRoles([]);
        $user->empresas()->detach();
    }
}
