<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EsBackoffice
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->esSuperadmin()) {
            abort(403, 'Acceso restringido.');
        }

        return $next($request);
    }
}
