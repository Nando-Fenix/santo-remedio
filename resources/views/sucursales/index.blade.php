@extends('layouts.app')

@section('title', 'Sucursales | Santo Remedio')
@section('page-title', 'Sucursales')
@section('page-subtitle', 'Administración de sucursales de la farmacia')

@section('content')

<div class="sucursal-page">

    <div class="compact-card sucursal-header">
        <div>
            <h2>
                <i class="bi bi-buildings"></i>
                Sucursales
            </h2>

            <p>
                Gestiona las sucursales disponibles para ventas, caja, compras e inventario.
            </p>
        </div>

        <a href="{{ route('sucursales.create') }}" class="btn-primary btn-mini">
            <i class="bi bi-plus-circle"></i>
            Nueva
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="compact-card sucursal-filter-card">
        <form method="GET" action="{{ route('sucursales.index') }}" class="sucursal-filter">
            <div class="form-group">
                <label>Buscar</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Nombre o dirección"
                >
            </div>

            <div class="sucursal-filter-actions">
                <button type="submit" class="btn-primary btn-mini" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('sucursales.index') }}" class="btn-secondary btn-mini" title="Limpiar">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="sucursal-list">
        @forelse ($sucursales as $sucursal)
            <div class="compact-card sucursal-item">
                <div class="sucursal-main">
                    <div class="sucursal-icon">
                        <i class="bi bi-building"></i>
                    </div>

                    <div>
                        <h3>{{ $sucursal->nombre }}</h3>

                        <p>
                            {{ $sucursal->direccion ?: 'Sin dirección registrada' }}
                        </p>
                    </div>
                </div>

                <div class="sucursal-meta">
                    @if ($sucursal->estado === 'activo')
                        <span class="badge badge-success">Activa</span>
                    @else
                        <span class="badge badge-danger">Inactiva</span>
                    @endif

                    <span class="badge badge-soft">
                        {{ $sucursal->usuarios_count }} usuario(s)
                    </span>
                </div>

                <div class="sucursal-actions">
                    <a
                        href="{{ route('sucursales.edit', $sucursal) }}"
                        class="icon-action icon-action-primary"
                        title="Editar"
                    >
                        <i class="bi bi-pencil-square"></i>
                    </a>

                    <form
                        action="{{ route('sucursales.destroy', $sucursal) }}"
                        method="POST"
                        onsubmit="return confirm('¿Deseas cambiar el estado de esta sucursal?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="icon-action {{ $sucursal->estado === 'activo' ? 'icon-action-danger' : 'icon-action-success' }}"
                            title="{{ $sucursal->estado === 'activo' ? 'Desactivar' : 'Activar' }}"
                        >
                            @if ($sucursal->estado === 'activo')
                                <i class="bi bi-toggle-off"></i>
                            @else
                                <i class="bi bi-toggle-on"></i>
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="compact-card sucursal-empty">
                <i class="bi bi-buildings"></i>
                <strong>No hay sucursales registradas.</strong>
                <span>Crea una nueva sucursal para comenzar.</span>
            </div>
        @endforelse
    </div>

    <div class="pagination-wrapper">
        {{ $sucursales->links() }}
    </div>

</div>

@endsection