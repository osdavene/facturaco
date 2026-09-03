<?php

namespace App\Console\Commands;

use App\Services\BackupSqlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FaccolBackupCommand extends Command
{
    protected $signature = 'faccol:backup {--output= : Directorio donde guardar el archivo de respaldo}';
    protected $description = 'Genera una copia de seguridad SQL completa de toda la plataforma FacCol';

    public function handle(BackupSqlService $backupService): int
    {
        $this->info('Iniciando copia de seguridad de FacCol...');

        $sql = $backupService->generar('CLI / Cron Job Automatizado');
        $nombreArchivo = 'backup_faccol_' . now()->format('Y-m-d_His') . '.sql';

        $dir = $this->option('output') ?: storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $rutaCompleta = $dir . '/' . $nombreArchivo;
        file_put_contents($rutaCompleta, $sql);

        $pesoMb = round(filesize($rutaCompleta) / (1024 * 1024), 2);

        $this->info("✓ Copia de seguridad generada con éxito:");
        $this->line("  Archivo: <comment>{$rutaCompleta}</comment>");
        $this->line("  Tamaño:  <comment>{$pesoMb} MB</comment>");

        return Command::SUCCESS;
    }
}
