@extends('layouts.app')

@section('title', 'Usuarios | Santo Remedio')
@section('page-title', 'Usuarios')
@section('page-subtitle', 'Gestión de accesos, roles, sucursales y permisos')

@section('content')

<div class="usuarios-page">

    <div class="compact-card usuarios-header">
        <div>
            <h2>
                <i class="bi bi-person-lock"></i>
                Usuarios
            </h2>

            <p>
                Administra accesos, roles, sucursales asignadas y permisos manuales.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('administrar_usuarios'))
            <a href="{{ route('usuarios.create') }}" class="btn-primary btn-mini">
                <i class="bi bi-plus-circle"></i>
                Nuevo
            </a>
        @endif
    </div>

    <div class="compact-card usuarios-filter-card">
        <form method="GET" action="{{ route('usuarios.index') }}" class="usuarios-filter">
            <div class="form-group">
                <label>Buscar</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Nombre, usuario, CI o rol"
                >
            </div>

            <div class="usuarios-filter-actions">
                <button type="submit" class="btn-primary btn-mini" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('usuarios.index') }}" class="btn-secondary btn-mini" title="Limpiar">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="usuarios-list">
        @forelse ($usuarios as $usuario)
            @php
                $sucursalPrincipal = $usuario->sucursales->firstWhere('pivot.principal', true);
                $totalSucursales = $usuario->sucursales->count();
                $esAdmin = $usuario->rol?->nombre === 'Administrador';
            @endphp

            <div class="compact-card usuario-item">
                <div class="usuario-main">
                    <div class="usuario-avatar-mini">
                        <i class="bi bi-person"></i>
                    </div>

                    <div>
                        <h3>{{ $usuario->nombre }}</h3>

                        <p>
                            <span>{{ $usuario->usuario }}</span>
                            <span>•</span>
                            <span>CI: {{ $usuario->ci }}</span>
                        </p>
                    </div>
                </div>

                <div class="usuario-info">
                    <span class="badge badge-primary">
                        {{ $usuario->rol->nombre ?? 'Sin rol' }}
                    </span>

                    @if ($usuario->estado === 'activo')
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-danger">Inactivo</span>
                    @endif
                </div>

                <div class="usuario-details">
                    <div>
                        <span>Sucursal principal</span>
                        <strong>{{ $sucursalPrincipal->nombre ?? 'Sin sucursal' }}</strong>
                    </div>

                    <div>
                        <span>Sucursales</span>
                        <strong>{{ $totalSucursales }}</strong>
                    </div>

                    <div>
                        <span>Permisos</span>

                        @if ($esAdmin)
                            <strong class="usuario-total-access">Acceso total</strong>
                        @else
                            <strong>{{ $usuario->permisosDirectos->count() }}</strong>
                        @endif
                    </div>
                </div>

                <div class="usuario-actions">
                    @if (auth()->user()->tienePermiso('administrar_usuarios'))
                        <a
                            href="{{ route('usuarios.edit', $usuario) }}"
                            class="icon-action icon-action-primary"
                            title="Editar"
                        >
                            <i class="bi bi-pencil-square"></i>
                        </a>

                        @if ($usuario->id !== auth()->id() && $usuario->estado === 'activo')
                            <form
                                method="POST"
                                action="{{ route('usuarios.destroy', $usuario) }}"
                                onsubmit="return confirmarFormulario(event, '¿Desactivar este usuario?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="icon-action icon-action-danger"
                                    title="Desactivar"
                                >
                                    <i class="bi bi-person-x"></i>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="compact-card usuarios-empty">
                <i class="bi bi-people"></i>
                <strong>No hay usuarios registrados.</strong>
                <span>Crea un usuario para empezar a administrar accesos.</span>
            </div>
        @endforelse
    </div>

    <div class="pagination-wrapper">
        {{ $usuarios->links() }}
    </div>

</div>

@endsection