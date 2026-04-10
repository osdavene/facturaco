<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use App\Observers\AuditoriaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        // Auditoría automática
        Cliente::observe(AuditoriaObserver::class);
        Proveedor::observe(AuditoriaObserver::class);
        Producto::observe(AuditoriaObserver::class);
        Categoria::observe(AuditoriaObserver::class);
        UnidadMedida::observe(AuditoriaObserver::class);
    }
}
