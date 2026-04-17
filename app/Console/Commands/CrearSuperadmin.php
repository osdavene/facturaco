<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CrearSuperadmin extends Command
{
    protected $signature = 'admin:superadmin
                            {--email=}
                            {--password=}
                            {--name=ADMINISTRADOR}';

    protected $description = 'Crea un administrador de plataforma (backoffice). NO es un usuario de empresa.';

    public function handle(): int
    {
        $email    = $this->option('email')    ?? $this->ask('Email del superadmin');
        $password = $this->option('password') ?? $this->secret('Contraseña (mín. 12 caracteres recomendado)');
        $name     = strtoupper($this->option('name'));

        if (strlen($password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');
            return 1;
        }

        if (User::where('email', $email)->exists()) {
            $this->warn('Ya existe un usuario con ese email.');
            return 1;
        }

        $user = User::create([
            'name'         => $name,
            'email'        => strtolower($email),
            'password'     => Hash::make($password),
            'activo'       => true,
            'is_superadmin'=> true,
        ]);

        // Sin roles Spatie — el acceso al backoffice es exclusivamente por is_superadmin=true.
        // Nunca vincular a empresas desde aquí.

        $this->info('✅ Superadmin de plataforma creado:');
        $this->line("   Email:    {$email}");
        $this->line("   Acceso:   /backoffice exclusivamente");
        $this->warn('   ⚠️  Este usuario NO puede acceder a ninguna empresa directamente.');
        $this->line('       Usa /backoffice → Impersonar para revisar una empresa.');

        return 0;
    }
}
