<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    /**
     * Verifica si el usuario autenticado tiene alguno de los roles permitidos.
     *
     * Uso en rutas:
     * ->middleware('rol:Administrador')
     * ->middleware('rol:Administrador,Vendedor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->rol) {
            abort(403, 'El usuario no tiene un rol asignado.');
        }

        if (!in_array($user->rol->nombre, $roles)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
