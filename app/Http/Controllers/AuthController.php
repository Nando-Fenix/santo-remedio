<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function mostrarLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'usuario' => ['required'],
            'password' => ['required'],
        ], [
            'usuario.required' => 'Ingrese su usuario.',
            'password.required' => 'Ingrese su contraseña.',
        ]);

        $recordar = $request->boolean('remember');

        if (Auth::attempt([
            'usuario' => $credenciales['usuario'],
            'password' => $credenciales['password'],
            'estado' => 'activo',
        ], $recordar)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->ultimo_acceso = now();
            $user->save();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no son correctas o el usuario está inactivo.',
        ])->onlyInput('usuario');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
