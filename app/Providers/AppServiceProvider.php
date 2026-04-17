<?php

namespace App\Providers;

use App\Models\ApiToken;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use App\Observers\AuditoriaObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        // Super-admin bypasses ALL permission checks regardless of cache state
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('propietario')) {
                return true;
            }
        });

        Sanctum::usePersonalAccessTokenModel(ApiToken::class);

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
