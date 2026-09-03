<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Factura;
use App\Models\Empresa;

class VerificarSistema extends Command
{
    protected $signature   = 'sistema:verificar';
    protected $description = 'Verifica el estado del sistema FacCol';

    public function handle(): int
    {
        $empresa = Empresa::obtener();
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('  FacCol — Estado del Sistema');
        $this->info('═══════════════════════════════════════');
        $this->info('  Empresa:  ' . $empresa->razon_social);
        $this->info('  NIT:      ' . $empresa->nit_formateado);
        $this->info('  Facturas: ' . Factura::count());
        $this->info('  DIAN:     ' . ($empresa->resolucion_vigente ? '✅ Vigente' : '❌ Vencida'));
        $this->info('═══════════════════════════════════════');
        return 0;
    }
}