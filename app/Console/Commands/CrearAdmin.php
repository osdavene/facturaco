<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CrearAdmin extends Command
{
    protected $signature = 'admin:crear
                            {--email=}
                            {--password=}
                            {--empresa=}
                            {--rol=propietario}';

    protected $description = 'Crea un usuario administrador para una empresa (propietario/admin)';

    public function handle(): int
    {
        $email    = $this->option('email')    ?? $this->ask('Email del usuario');
        $password = $this->option('password') ?? $this->secret('Contraseña (mín. 8 caracteres)');
        $rol      = $this->option('rol');

        if (strlen($password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');
            return 1;
        }

        if (User::where('email', $email)->exists()) {
            $this->warn('Ya existe un usuario con ese email.');
            return 1;
        }

        $rolesValidos = ['propietario', 'admin', 'vendedor', 'bodeguero', 'contador', 'solo-lectura'];
        if (!in_array($rol, $rolesValidos)) {
            $this->error('Rol inválido. Opciones: ' . implode(', ', $rolesValidos));
            return 1;
        }

        $user = User::create([
            'name'     => 'ADMINISTRADOR',
            'email'    => strtolower($email),
            'password' => Hash::make($password),
            'activo'   => true,
        ]);

        $user->assignRole($rol);

        // Vincular a empresa si se especificó
        $empresaId = $this->option('empresa');
        if ($empresaId) {
            $empresa = Empresa::find($empresaId);
            if (!$empresa) {
                $this->warn("Empresa ID {$empresaId} no encontrada. Usuario creado sin empresa.");
            } else {
                $rolPivot = in_array($rol, ['propietario', 'admin']) ? 'admin' : 'operador';
                $empresa->usuarios()->attach($user->id, ['rol' => $rolPivot, 'activo' => true]);
                $this->line("   Empresa:  {$empresa->razon_social}");
            }
        }

        $this->info('✅ Usuario de empresa creado:');
        $this->line("   Email:    {$email}");
        $this->line("   Rol:      {$rol}");

        return 0;
    }
}
