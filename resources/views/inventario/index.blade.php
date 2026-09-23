@extends('layouts.app')

@section('title', 'Inventario | Santo Remedio')
@section('page-title', 'Inventario')
@section('page-subtitle', 'Control de stock por sucursal, lote y vencimiento')

@section('content')

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-box-seam"></i>
                Stock actual
            </h2>

            <p>
                Consulta de productos disponibles por sucursal, lote y vencimiento.
            </p>
        </div>

        <div class="detail-actions">
            @if (auth()->user()->tienePermiso('ver_movimientos_inventario'))
                <a href="{{ route('inventario.movimientos') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left-right"></i>
                    Movimientos
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_inventario'))
                <a href="{{ route('inventario.proximos-vencer') }}" class="btn-secondary">
                    <i class="bi bi-calendar-warning"></i>
                    Próximos
                </a>

                <a href="{{ route('inventario.productos-vencidos') }}" class="btn-danger">
                    <i class="bi bi-exclamation-octagon"></i>
                    Vencidos
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_bajas_inventario'))
                <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                    <i class="bi bi-archive"></i>
                    Bajas
                </a>
            @endif

            @if (auth()->user()->tienePermiso('registrar_baja_inventario'))
                <a href="{{ route('bajas-inventario.create') }}" class="btn-primary">
                    <i class="bi bi-dash-circle"></i>
                    Registrar baja
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ajustar_inventario'))
                <a href="{{ route('inventario.create') }}" class="btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Entrada
                </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('inventario.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar producto</label>
            <input
                type="text"
                name="buscar"
                value="{{ $buscar ?? '' }}"
                placeholder="Nombre comercial, genérico o concentración"
            >
        </div>

        <div class="form-group">
            <label>Sucursal</label>
            <select name="sucursal_id">
                <option value="">Todas las sucursales</option>

                @foreach ($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}" @selected(($sucursalId ?? '') == $sucursal->id)>
                        {{ $sucursal->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Categoría</label>
            <select name="categoria_id">
                <option value="">Todas las categorías</option>

                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(($categoriaId ?? '') == $categoria->id)>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado_stock">
                <option value="">Todos</option>
                <option value="disponible" @selected(($estadoStock ?? '') === 'disponible')>Disponible</option>
                <option value="bajo" @selected(($estadoStock ?? '') === 'bajo')>Stock bajo</option>
                <option value="agotado" @selected(($estadoStock ?? '') === 'agotado')>Agotado</option>
            </select>
        </div>

        <div class="form-group">
            <label>Vencimiento</label>
            <select name="estado_vencimiento">
                <option value="">Todos</option>
                <option value="vigente" @selected(($estadoVencimiento ?? '') === 'vigente')>Vigente</option>
                <option value="proximo" @selected(($estadoVencimiento ?? '') === 'proximo')>Próximo a vencer</option>
                <option value="vencido" @selected(($estadoVencimiento ?? '') === 'vencido')>Vencido</option>
                <option value="sin_fecha" @selected(($estadoVencimiento ?? '') === 'sin_fecha')>Sin fecha</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('inventario.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table inventory-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Sucursal</th>
                    <th>Lote</th>
                    <th>Vencimiento</th>
                    <th>Stock</th>
                    <th>Mín.</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($inventarios as $inventario)
                    <tr>
                        <td>
                            <strong>{{ $inventario->producto->nombre_comercial ?? '-' }}</strong>

                            @if ($inventario->producto?->nombre_generico)
                                <br>
                                <small style="color:#6B7280;">
                                    Genérico: {{ $inventario->producto->nombre_generico }}
                                </small>
                            @endif

                            @if ($inventario->producto?->concentracion)
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $inventario->producto->concentracion }}
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ $inventario->sucursal->nombre ?? '-' }}
                        </td>

                        <td>
                            <strong>{{ $inventario->lote->numero_lote ?? 'Sin lote' }}</strong>
                        </td>

                        <td>
                            @if ($inventario->lote?->fecha_vencimiento)
                                {{ $inventario->lote->fecha_vencimiento->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if ($inventario->stock_actual <= 0)
                                <strong class="inventory-stock-empty">
                                    {{ $inventario->stock_actual }}
                                </strong>
                            @elseif ($inventario->stock_actual <= $inventario->stock_minimo)
                                <strong class="inventory-stock-low">
                                    {{ $inventario->stock_actual }}
                                </strong>
                            @else
                                <strong class="inventory-stock-ok">
                                    {{ $inventario->stock_actual }}
                                </strong>
                            @endif
                        </td>

                        <td>
                            {{ $inventario->stock_minimo }}
                        </td>

                        <td>
                            @if ($inventario->stock_actual <= 0)
                                <span class="badge badge-danger">Agotado</span>
                            @elseif ($inventario->stock_actual <= $inventario->stock_minimo)
                                <span class="badge badge-warning">Stock bajo</span>
                            @else
                                <span class="badge badge-success">Disponible</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                @if (auth()->user()->tienePermiso('editar_producto'))
                                    <a href="{{ route('productos.edit', $inventario->producto_id) }}"
                                    class="icon-action"
                                    title="Editar producto">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                @if (auth()->user()->tienePermiso('ajustar_inventario'))
                                    <a href="{{ route('inventario.create', ['producto_id' => $inventario->producto_id]) }}"
                                    class="icon-action"
                                    title="Agregar entrada">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                @endif

                                @if (auth()->user()->tienePermiso('registrar_baja_inventario'))
                                    <a href="{{ route('bajas-inventario.create', ['inventario_id' => $inventario->id]) }}"
                                    class="icon-action danger"
                                    title="Registrar baja">
                                        <i class="bi bi-dash-circle"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-table-message">
                            Todavía no hay inventario registrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $inventarios->links() }}
    </div>

</div>

@endsection