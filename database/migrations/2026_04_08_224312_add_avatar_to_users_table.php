<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('activo');
            $table->string('telefono', 20)->nullable()->after('avatar');
            $table->string('cargo', 100)->nullable()->after('telefono');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar','telefono','cargo']);
        });
    }
};