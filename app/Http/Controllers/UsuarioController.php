<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
     public function index()
    {
        $usuarios = User::with([
                'rol',
                'sucursales',
                'permisosDirectos',
            ])
            ->orderBy('nombre')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Rol::orderBy('nombre')->get();

        $sucursales = Sucursal::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $permisos = Permiso::orderBy('modulo')
            ->orderBy('descripcion')
            ->get()
            ->groupBy('modulo');

        return view('usuarios.create', compact(
            'roles',
            'sucursales',
            'permisos'
        ));
    }

    /**
     * Crea un usuario y asigna permisos directos seleccionados.
     *
     * Reglas:
     * - El CI no debe repetirse.
     * - El usuario/login no debe repetirse.
     * - La contraseña se guarda hasheada.
     * - La sucursal principal se marca en la tabla pivote usuario_sucursal.
     * - Los permisos directos se guardan en usuario_permiso.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'ci' => ['required', 'string', 'max:30', 'unique:users,ci'],
            'usuario' => ['required', 'string', 'max:80', 'unique:users,usuario'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],

            'rol_id' => ['required', 'exists:roles,id'],
            'sucursal_principal_id' => ['required', 'exists:sucursales,id'],
            'sucursales' => ['nullable', 'array'],
            'sucursales.*' => ['exists:sucursales,id'],

            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['exists:permisos,id'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'ci.required' => 'El CI es obligatorio.',
            'ci.unique' => 'Ya existe un usuario con ese CI.',
            'usuario.required' => 'El usuario de acceso es obligatorio.',
            'usuario.unique' => 'Ese usuario ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'rol_id.required' => 'Debe seleccionar un rol.',
            'sucursal_principal_id.required' => 'Debe seleccionar una sucursal principal.',
        ]);

        $user = User::create([
            'nombre' => $datos['nombre'],
            'ci' => $datos['ci'],
            'usuario' => $datos['usuario'],
            'password' => Hash::make($datos['password']),
            'rol_id' => $datos['rol_id'],
            'estado' => 'activo',
        ]);

        $sucursalesSeleccionadas = collect($datos['sucursales'] ?? [])
            ->push($datos['sucursal_principal_id'])
            ->unique()
            ->values();

        $syncSucursales = [];

        foreach ($sucursalesSeleccionadas as $sucursalId) {
            $syncSucursales[$sucursalId] = [
                'principal' => (int) $sucursalId === (int) $datos['sucursal_principal_id'],
                'estado' => 'activo',
            ];
        }

        $user->sucursales()->sync($syncSucursales);

        $user->permisosDirectos()->sync($datos['permisos'] ?? []);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $user->load([
            'rol',
            'sucursales',
            'permisosDirectos',
        ]);

        $roles = Rol::orderBy('nombre')->get();

        $sucursales = Sucursal::where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $permisos = Permiso::orderBy('modulo')
            ->orderBy('descripcion')
            ->get()
            ->groupBy('modulo');

        $sucursalesUsuario = $user->sucursales->pluck('id')->toArray();

        $sucursalPrincipalId = optional(
            $user->sucursales->firstWhere('pivot.principal', true)
        )->id;

        $permisosUsuario = $user->permisosDirectos->pluck('id')->toArray();

        return view('usuarios.edit', compact(
            'user',
            'roles',
            'sucursales',
            'permisos',
            'sucursalesUsuario',
            'sucursalPrincipalId',
            'permisosUsuario'
        ));
    }

    /**
     * Actualiza datos, sucursales y permisos de un usuario.
     *
     * La contraseña solo se cambia si se ingresa una nueva.
     */
    public function update(Request $request, User $user)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'ci' => [
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'ci')->ignore($user->id),
            ],
            'usuario' => [
                'required',
                'string',
                'max:80',
                Rule::unique('users', 'usuario')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],

            'rol_id' => ['required', 'exists:roles,id'],
            'sucursal_principal_id' => ['required', 'exists:sucursales,id'],
            'sucursales' => ['nullable', 'array'],
            'sucursales.*' => ['exists:sucursales,id'],

            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['exists:permisos,id'],

            'estado' => ['required', 'in:activo,inactivo'],
        ], [
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $user->update([
            'nombre' => $datos['nombre'],
            'ci' => $datos['ci'],
            'usuario' => $datos['usuario'],
            'rol_id' => $datos['rol_id'],
            'estado' => $datos['estado'],
        ]);

        if (!empty($datos['password'])) {
            $user->update([
                'password' => Hash::make($datos['password']),
            ]);
        }

        $sucursalesSeleccionadas = collect($datos['sucursales'] ?? [])
            ->push($datos['sucursal_principal_id'])
            ->unique()
            ->values();

        $syncSucursales = [];

        foreach ($sucursalesSeleccionadas as $sucursalId) {
            $syncSucursales[$sucursalId] = [
                'principal' => (int) $sucursalId === (int) $datos['sucursal_principal_id'],
                'estado' => 'activo',
            ];
        }

        $user->sucursales()->sync($syncSucursales);

        $user->permisosDirectos()->sync($datos['permisos'] ?? []);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'No puede desactivar su propio usuario.');
        }

        $user->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    public function rolRapido(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:roles,nombre'],
        ]);

        $rol = Rol::create([
            'nombre' => $datos['nombre'],
            'descripcion' => 'Rol creado desde el formulario de usuarios',
            'estado' => 'activo',
        ]);

        return response()->json([
            'rol' => [
                'id' => $rol->id,
                'nombre' => $rol->nombre,
            ],
        ]);
    }
}
