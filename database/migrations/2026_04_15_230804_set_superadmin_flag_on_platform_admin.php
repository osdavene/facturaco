<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // El usuario admin@facturaco.com es el superadmin de plataforma (backoffice).
        // Debe tener is_superadmin=true para no aparecer en las listas de usuarios de empresa.
        DB::table('users')
            ->where('email', 'admin@facturaco.com')
            ->update(['is_superadmin' => true]);
    }

    public function down(): void
    {
        //
    }
};
