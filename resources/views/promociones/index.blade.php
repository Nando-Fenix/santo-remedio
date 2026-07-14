@extends('layouts.app')

@section('title', 'Promociones | Santo Remedio')
@section('page-title', 'Promociones')
@section('page-subtitle', 'Ofertas, combos y promociones por vencimiento')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Listado de promociones</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Administre promociones individuales, combos y productos próximos a vencer.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('crear_promocion'))
            <a href="{{ route('promociones.create') }}" class="btn-primary">
                + Nueva promoción
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('promociones.index') }}" style="margin-bottom: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar promoción</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Nombre, descripción o motivo"
                >
            </div>

            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="producto_individual" @selected(($tipo ?? '') === 'producto_individual')>Producto individual</option>
                    <option value="combo" @selected(($tipo ?? '') === 'combo')>Combo</option>
                    <option value="por_vencimiento" @selected(($tipo ?? '') === 'por_vencimiento')>Por vencimiento</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="activo" @selected(($estado ?? '') === 'activo')>Activo</option>
                    <option value="inactivo" @selected(($estado ?? '') === 'inactivo')>Inactivo</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('promociones.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Promoción</th>
                    <th>Tipo</th>
                    <th>Precio promocional</th>
                    <th>Vigencia</th>
                    <th>Productos incluidos</th>
                    <th>Sucursal</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($promociones as $promocion)
                    <tr>
                        <td>
                            <strong>{{ $promocion->nombre }}</strong>

                            @if ($promocion->descripcion)
                                <br>
                                <small style="color: #6B7280;">
                                    {{ $promocion->descripcion }}
                                </small>
                            @endif
                        </td>

                        <td>
                            @if ($promocion->tipo === 'producto_individual')
                                Producto individual
                            @elseif ($promocion->tipo === 'combo')
                                Combo
                            @elseif ($promocion->tipo === 'por_vencimiento')
                                Por vencimiento
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <strong>{{ number_format($promocion->precio_promocional, 2) }} Bs</strong>
                        </td>

                        <td>
                            @if ($promocion->fecha_inicio || $promocion->fecha_fin)
                                {{ $promocion->fecha_inicio ? $promocion->fecha_inicio->format('d/m/Y') : 'Sin inicio' }}
                                -
                                {{ $promocion->fecha_fin ? $promocion->fecha_fin->format('d/m/Y') : 'Sin fin' }}
                            @else
                                Sin vigencia definida
                            @endif
                        </td>

                        <td>
                            {{ $promocion->items_count }}
                        </td>

                        <td>
                            {{ $promocion->sucursal->nombre ?? 'Todas' }}
                        </td>

                        <td>
                            <span class="badge {{ $promocion->estado === 'activo' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($promocion->estado) }}
                            </span>
                        </td>

                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @if (auth()->user()->tienePermiso('ver_promociones'))
                                    <a href="{{ route('promociones.show', $promocion) }}" class="btn-secondary">
                                        Ver
                                    </a>
                                @endif

                                @if ($promocion->estado === 'activo' && auth()->user()->tienePermiso('editar_promocion'))
                                    <a href="{{ route('promociones.edit', $promocion) }}" class="btn-secondary">
                                        Editar
                                    </a>
                                @endif

                                @if ($promocion->estado === 'activo' && auth()->user()->tienePermiso('desactivar_promocion'))
                                    <form method="POST"
                                        action="{{ route('promociones.destroy', $promocion) }}"
                                        onsubmit="return confirmarFormulario(event, '¿Desea desactivar esta promoción?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-danger">
                                            Desactivar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #6B7280;">
                            Todavía no hay promociones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $promociones->links() }}
    </div>

</div>

@endsection