<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Registrar login
        LoginLog::create([
            'user_id'    => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'navegador'  => $this->parsearNavegador($request->userAgent() ?? ''),
            'dispositivo'=> $this->parsearDispositivo($request->userAgent() ?? ''),
            'accion'     => 'login',
            'fecha_hora' => now(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Registrar logout antes de cerrar sesión
        if (Auth::check()) {
            LoginLog::create([
                'user_id'    => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'navegador'  => $this->parsearNavegador($request->userAgent() ?? ''),
                'dispositivo'=> $this->parsearDispositivo($request->userAgent() ?? ''),
                'accion'     => 'logout',
                'fecha_hora' => now(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function parsearNavegador(string $ua): string
    {
        if (str_contains($ua, 'Edg'))     return 'Edge';
        if (str_contains($ua, 'OPR'))     return 'Opera';
        if (str_contains($ua, 'Chrome'))  return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari'))  return 'Safari';
        return 'Desconocido';
    }

    private function parsearDispositivo(string $ua): string
    {
        if (str_contains($ua, 'iPhone') || (str_contains($ua, 'Android') && str_contains($ua, 'Mobile'))) return 'Móvil';
        if (str_contains($ua, 'iPad')   || str_contains($ua, 'Android')) return 'Tablet';
        return 'Escritorio';
    }
}