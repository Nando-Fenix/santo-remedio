<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    /**
     * Verifica si el usuario tiene un permiso específico.
     *
     * Uso:
     * ->middleware('permiso:realizar_venta')
     * ->middleware('permiso:administrar_usuarios')
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->tienePermiso($permiso)) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        return $next($request);
    }
}