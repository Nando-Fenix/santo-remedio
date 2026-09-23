@extends('layouts.app')

@section('title', 'Movimientos de inventario | Santo Remedio')
@section('page-title', 'Movimientos de inventario')
@section('page-subtitle', 'Historial de entradas, salidas y ajustes de stock')

@section('content')

@if (!auth()->user()->tienePermiso('ver_movimientos_inventario'))
    <div class="alert-danger">
        No tiene permiso para ver movimientos de inventario.
    </div>
@else

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-arrow-left-right"></i>
                Historial de inventario
            </h2>

            <p>
                Revisión de entradas, salidas, devoluciones, bajas y ajustes realizados en stock.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('ver_inventario'))
            <a href="{{ route('inventario.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver al inventario
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('inventario.movimientos') }}" class="filter-bar">
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
                <option value="">Todas</option>

                @foreach ($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}" @selected(($sucursalId ?? '') == $sucursal->id)>
                        {{ $sucursal->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Tipo</label>
            <select name="tipo_movimiento">
                <option value="">Todos</option>
                <option value="entrada" @selected(($tipoMovimiento ?? '') === 'entrada')>Entrada</option>
                <option value="salida" @selected(($tipoMovimiento ?? '') === 'salida')>Salida</option>
                <option value="ajuste_positivo" @selected(($tipoMovimiento ?? '') === 'ajuste_positivo')>Ajuste positivo</option>
                <option value="ajuste_negativo" @selected(($tipoMovimiento ?? '') === 'ajuste_negativo')>Ajuste negativo</option>
                <option value="devolucion" @selected(($tipoMovimiento ?? '') === 'devolucion')>Devolución</option>
                <option value="vencimiento" @selected(($tipoMovimiento ?? '') === 'vencimiento')>Vencimiento</option>
                <option value="daño" @selected(($tipoMovimiento ?? '') === 'daño')>Daño</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('inventario.movimientos') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table inventory-movements-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Sucursal</th>
                    <th>Lote</th>
                    <th>Tipo</th>
                    <th>Cant.</th>
                    <th>Anterior</th>
                    <th>Nuevo</th>
                    <th>Usuario</th>
                    <th>Motivo</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($movimientos as $movimiento)
                    <tr>
                        <td>
                            {{ $movimiento->created_at->format('d/m/Y') }}
                            <br>
                            <small style="color:#6B7280;">
                                {{ $movimiento->created_at->format('H:i') }}
                            </small>
                        </td>

                        <td>
                            <strong>{{ $movimiento->producto->nombre_comercial ?? '-' }}</strong>

                            @if($movimiento->producto?->concentracion)
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $movimiento->producto->concentracion }}
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ $movimiento->sucursal->nombre ?? '-' }}
                        </td>

                        <td>
                            <strong>{{ $movimiento->lote->numero_lote ?? 'Sin lote' }}</strong>
                        </td>

                        <td>
                            @if (in_array($movimiento->tipo_movimiento, ['entrada', 'ajuste_positivo', 'devolucion']))
                                <span class="badge badge-success">
                                    {{ str_replace('_', ' ', ucfirst($movimiento->tipo_movimiento)) }}
                                </span>
                            @elseif (in_array($movimiento->tipo_movimiento, ['salida', 'ajuste_negativo', 'vencimiento', 'daño']))
                                <span class="badge badge-danger">
                                    {{ str_replace('_', ' ', ucfirst($movimiento->tipo_movimiento)) }}
                                </span>
                            @else
                                <span class="badge badge-soft">
                                    {{ str_replace('_', ' ', ucfirst($movimiento->tipo_movimiento)) }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if (in_array($movimiento->tipo_movimiento, ['entrada', 'ajuste_positivo', 'devolucion']))
                                <strong class="inventory-movement-positive">
                                    +{{ $movimiento->cantidad }}
                                </strong>
                            @elseif (in_array($movimiento->tipo_movimiento, ['salida', 'ajuste_negativo', 'vencimiento', 'daño']))
                                <strong class="inventory-movement-negative">
                                    -{{ $movimiento->cantidad }}
                                </strong>
                            @else
                                <strong>
                                    {{ $movimiento->cantidad }}
                                </strong>
                            @endif
                        </td>

                        <td>
                            {{ $movimiento->stock_anterior }}
                        </td>

                        <td>
                            <strong>{{ $movimiento->stock_nuevo }}</strong>
                        </td>

                        <td>
                            {{ $movimiento->usuario->nombre ?? '-' }}
                        </td>

                        <td>
                            <span class="inventory-movement-reason">
                                {{ $movimiento->motivo ?? '-' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="empty-table-message">
                            No hay movimientos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $movimientos->links() }}
    </div>

</div>

@endif

@endsection