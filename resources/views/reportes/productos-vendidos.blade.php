@extends('layouts.app')

@section('title', 'Productos vendidos | Santo Remedio')
@section('page-title', 'Productos vendidos')
@section('page-subtitle', 'Ranking de productos con mayor venta e ingreso')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-trophy"></i>
                Productos vendidos
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
                |
                Periodo:
                <strong>{{ $fechaInicio }}</strong>
                al
                <strong>{{ $fechaFin }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.productos-vendidos.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.productos-vendidos.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.productos-vendidos') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
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

                <a href="{{ route('reportes.productos-vendidos') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Productos distintos</span>
            <strong>{{ $resumen['productos_distintos'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Cantidad vendida</span>
            <strong>{{ $resumen['cantidad_vendida'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Unidades descontadas</span>
            <strong>{{ $resumen['unidades_vendidas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Total generado</span>
            <strong>{{ number_format($resumen['total_generado'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-ol"></i>
                Ranking de productos
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-products-sold-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th>Presentación</th>
                        <th>Cant.</th>
                        <th>Unid.</th>
                        <th>Descuento</th>
                        <th>Total generado</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($productosPaginados as $item)
                        <tr>
                            <td>
                                <span class="ranking-number">
                                    {{ $productosPaginados->firstItem() + $loop->index }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $item['producto']->nombre_comercial ?? '-' }}</strong>

                                @if (($item['producto']->nombre_generico ?? null) || ($item['producto']->concentracion ?? null))
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $item['producto']->nombre_generico ?? '' }}
                                        {{ $item['producto']->concentracion ?? '' }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $item['producto']->laboratorio->nombre ?? '-' }}
                            </td>

                            <td>
                                <span class="badge badge-soft">
                                    {{ $item['presentacion']->presentacion->nombre ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $item['cantidad_vendida'] }}</strong>
                            </td>

                            <td>
                                {{ $item['unidades_vendidas'] }}
                            </td>

                            <td>
                                {{ number_format($item['descuento'], 2) }} Bs
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($item['total'], 2) }} Bs
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay productos vendidos en este rango.
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