@extends('layouts.app')

@section('title', 'Productos a reponer | Santo Remedio')
@section('page-title', 'Productos a reponer')
@section('page-subtitle', 'Stock bajo combinado con historial de ventas')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-arrow-repeat"></i>
                Productos a reponer
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
                |
                Análisis:
                <strong>Últimos {{ $dias }} días</strong>
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.productos-reponer.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.productos-reponer.exportar-csv', request()->query()) }}"
                class="btn-secondary"
            >
                <i class="bi bi-file-earmark-excel"></i>
                CSV
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Reportes
            </a>
        </div>
    </div>

    <div class="compact-card report-filter-card">
        <form method="GET" action="{{ route('reportes.productos-reponer') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Periodo</label>
                <select name="dias">
                    <option value="7" @selected($dias == 7)>Últimos 7 días</option>
                    <option value="15" @selected($dias == 15)>Últimos 15 días</option>
                    <option value="30" @selected($dias == 30)>Últimos 30 días</option>
                    <option value="60" @selected($dias == 60)>Últimos 60 días</option>
                    <option value="90" @selected($dias == 90)>Últimos 90 días</option>
                </select>
            </div>

            <div class="form-group filter-search">
                <label>Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, genérico, concentración o laboratorio"
                >
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.productos-reponer') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="critical-summary-strip">
        <div class="critical-mini-stat critical-soft">
            <span>Productos a reponer</span>
            <strong>{{ $resumen['productos_reponer'] }}</strong>
        </div>

        <div class="critical-mini-stat critical-danger">
            <span>Agotados</span>
            <strong>{{ $resumen['agotados'] }}</strong>
        </div>

        <div class="critical-mini-stat critical-warning">
            <span>Stock bajo</span>
            <strong>{{ $resumen['stock_bajo'] }}</strong>
        </div>

        <div class="critical-mini-stat">
            <span>Cantidad sugerida</span>
            <strong>{{ $resumen['cantidad_sugerida_total'] }}</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <div>
                <h3>
                    <i class="bi bi-list-check"></i>
                    Detalle de reposición sugerida
                </h3>

                <small style="color:#6B7280;">
                    La cantidad sugerida es referencial y busca alcanzar aproximadamente el doble del stock mínimo.
                </small>
            </div>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-reorder-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th>Stock</th>
                        <th>Mín.</th>
                        <th>Vendidas</th>
                        <th>Prom. diario</th>
                        <th>Sugerido</th>
                        <th>Prioridad</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($productosPaginados as $item)
                        @php
                            $inventario = $item['inventario'];
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $inventario->producto->nombre_comercial ?? '-' }}</strong>

                                @if (($inventario->producto->nombre_generico ?? null) || ($inventario->producto->concentracion ?? null))
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $inventario->producto->nombre_generico ?? '' }}
                                        {{ $inventario->producto->concentracion ?? '' }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $inventario->producto->laboratorio->nombre ?? '-' }}
                            </td>

                            <td>
                                @if ($inventario->stock_actual <= 0)
                                    <strong class="critical-stock-danger">
                                        {{ $inventario->stock_actual }}
                                    </strong>
                                @elseif ($inventario->stock_actual <= $inventario->stock_minimo)
                                    <strong class="critical-stock-warning">
                                        {{ $inventario->stock_actual }}
                                    </strong>
                                @else
                                    <strong>{{ $inventario->stock_actual }}</strong>
                                @endif
                            </td>

                            <td>
                                {{ $inventario->stock_minimo }}
                            </td>

                            <td>
                                <strong>{{ $item['unidades_vendidas'] }}</strong>
                            </td>

                            <td>
                                {{ number_format($item['promedio_diario'], 2) }}
                            </td>

                            <td>
                                <span class="reorder-suggested">
                                    {{ $item['cantidad_sugerida'] }}
                                </span>
                            </td>

                            <td>
                                @if ($item['prioridad'] === 'Alta')
                                    <span class="badge badge-danger">Alta</span>
                                @elseif ($item['prioridad'] === 'Media')
                                    <span class="badge badge-warning">Media</span>
                                @else
                                    <span class="badge badge-soft">Baja</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay productos para reponer según los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $productosPaginados->links() }}
        </div>
    </div>

</div>

@endsection