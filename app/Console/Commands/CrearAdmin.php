<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CrearAdmin extends Command
{
    protected $signature   = 'admin:crear {--email=admin@facturaco.co} {--password=Admin123*}';
    protected $description = 'Crea el usuario administrador inicial';

    public function handle()
    {
        $email    = $this->option('email');
        $password = $this->option('password');

        if (User::where('email', $email)->exists()) {
            $this->warn('Ya existe un usuario con ese email.');
            return 1;
        }

        $user = User::create([
            'name'     => 'ADMINISTRADOR',
            'email'    => $email,
            'password' => Hash::make($password),
            'activo'   => true,
        ]);

        $user->assignRole('super-admin');

        $this->info('✅ Usuario admin creado:');
        $this->info('   Email:    ' . $email);
        $this->info('   Password: ' . $password);
        $this->info('   Rol:      super-admin');
        return 0;
    }
}