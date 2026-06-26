@extends('layouts.app')

@section('title', 'Usuarios | Santo Remedio')
@section('page-title', 'Usuarios')
@section('page-subtitle', 'Gestión de usuarios, roles, sucursales y permisos')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Usuarios del sistema</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Administre accesos, roles, sucursales y permisos personalizados.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('administrar_usuarios'))
            <a href="{{ route('usuarios.create') }}" class="btn-primary">
                + Nuevo usuario
            </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>CI</th>
                    <th>Rol</th>
                    <th>Sucursales</th>
                    <th>Permisos directos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>
                            <strong>{{ $usuario->nombre }}</strong>
                            <br>
                            <small style="color:#6B7280;">
                                Acceso: {{ $usuario->usuario }}
                            </small>
                        </td>

                        <td>{{ $usuario->ci }}</td>

                        <td>
                            <span class="badge badge-primary">
                                {{ $usuario->rol->nombre ?? 'Sin rol' }}
                            </span>
                        </td>

                        <td>
                            @forelse ($usuario->sucursales as $sucursal)
                                <span class="badge {{ $sucursal->pivot->principal ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $sucursal->nombre }}
                                    {{ $sucursal->pivot->principal ? '(Principal)' : '' }}
                                </span>
                            @empty
                                <span style="color:#6B7280;">Sin sucursal</span>
                            @endforelse
                        </td>

                        <td>
                            @if ($usuario->rol?->nombre === 'Administrador')
                                <span class="badge badge-success">Acceso total</span>
                            @else
                                <span class="badge badge-secondary">
                                    {{ $usuario->permisosDirectos->count() }} permisos directos
                                </span>
                            @endif
                        </td>

                        <td>
                            @if ($usuario->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                @if (auth()->user()->tienePermiso('administrar_usuarios'))
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn-secondary">
                                        Editar
                                    </a>

                                    @if ($usuario->id !== auth()->id() && $usuario->estado === 'activo')
                                        <form method="POST"
                                            action="{{ route('usuarios.destroy', $usuario) }}"
                                            onsubmit="return confirmarFormulario(event, '¿Desactivar este usuario?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-danger">
                                                Desactivar
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection