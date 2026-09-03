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
        config([
            'cache.default'          => 'array',
            'permission.cache.store' => 'array',
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            app(\Spatie\Permission\PermissionRegistrar::class)->setCacheStore('array');
        } catch (\Throwable) {}

        // Super-admin, propietario y admin de empresa tienen acceso total a todos los permisos
        Gate::before(function ($user, $ability) {
            if ($user->esSuperadmin() || $user->hasRole('propietario') || $user->hasRole('admin') || $user->esAdminEmpresa()) {
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

        // Registro automático de accesos (Login / Logout)
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            try {
                if (! $event->user) return;
                $req = request();
                $ua = $req ? $req->userAgent() : '';
                [$navegador, $dispositivo] = \App\Models\LoginLog::parsearUserAgent($ua);

                \App\Models\LoginLog::create([
                    'user_id'     => $event->user->id,
                    'ip_address'  => $req ? $req->ip() : '127.0.0.1',
                    'user_agent'  => $ua,
                    'navegador'   => $navegador,
                    'dispositivo' => $dispositivo,
                    'accion'      => 'login',
                    'fecha_hora'  => now('America/Bogota'),
                ]);
            } catch (\Throwable) {}
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            try {
                if (! $event->user) return;
                $req = request();
                $ua = $req ? $req->userAgent() : '';
                [$navegador, $dispositivo] = \App\Models\LoginLog::parsearUserAgent($ua);

                \App\Models\LoginLog::create([
                    'user_id'     => $event->user->id,
                    'ip_address'  => $req ? $req->ip() : '127.0.0.1',
                    'user_agent'  => $ua,
                    'navegador'   => $navegador,
                    'dispositivo' => $dispositivo,
                    'accion'      => 'logout',
                    'fecha_hora'  => now('America/Bogota'),
                ]);
            } catch (\Throwable) {}
        });
    }
}
