@extends('layouts.app')

@section('title', 'Utilidad estimada | Santo Remedio')
@section('page-title', 'Utilidad estimada')
@section('page-subtitle', 'Ganancia aproximada según precio de venta y precio de compra')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-graph-up-arrow"></i>
                Utilidad estimada
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
                href="{{ route('reportes.utilidad-estimada.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.utilidad-estimada.exportar-csv', request()->query()) }}"
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

    <div class="report-warning-note">
        <i class="bi bi-info-circle"></i>
        <span>
            Este reporte es referencial. La precisión depende del precio de compra registrado en cada presentación.
        </span>
    </div>

    <div class="compact-card report-filter-card">
        <form method="GET" action="{{ route('reportes.utilidad-estimada') }}" class="filter-bar report-filter-bar">
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

                <a href="{{ route('reportes.utilidad-estimada') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="profit-main-summary">
        <div class="profit-total-card">
            <span>Total venta</span>
            <strong>{{ number_format($resumen['total_venta'], 2) }} Bs</strong>
            <small>{{ $resumen['productos_distintos'] }} producto(s) vendido(s)</small>
        </div>

        <div class="profit-total-card profit-cost-card">
            <span>Costo estimado</span>
            <strong>{{ number_format($resumen['costo_estimado'], 2) }} Bs</strong>
            <small>Según precio de compra registrado</small>
        </div>

        <div class="profit-total-card profit-gain-card">
            <span>Utilidad estimada</span>
            <strong>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</strong>
            <small>Ganancia referencial</small>
        </div>

        <div class="profit-total-card profit-margin-card">
            <span>Margen general</span>
            <strong>{{ number_format($resumen['margen_general'], 2) }}%</strong>
            <small>Margen estimado general</small>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Productos vendidos</span>
            <strong>{{ $resumen['productos_distintos'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Total venta</span>
            <strong>{{ number_format($resumen['total_venta'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Costo estimado</span>
            <strong>{{ number_format($resumen['costo_estimado'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Utilidad estimada</span>
            <strong>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle de utilidad por producto
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-profit-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Laboratorio</th>
                        <th>Presentación</th>
                        <th>Venta</th>
                        <th>Costo</th>
                        <th>Utilidad</th>
                        <th>Margen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($utilidadesPaginadas as $item)
                        <tr>
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
                                <strong class="report-money">
                                    {{ number_format($item['total_venta'], 2) }} Bs
                                </strong>
                                <br>
                                <small style="color:#6B7280;">
                                    Cant.: {{ $item['cantidad_vendida'] }}
                                </small>
                            </td>

                            <td>
                                {{ number_format($item['costo_estimado'], 2) }} Bs
                            </td>

                            <td>
                                @if ($item['utilidad_estimada'] < 0)
                                    <strong class="profit-negative">
                                        {{ number_format($item['utilidad_estimada'], 2) }} Bs
                                    </strong>
                                @else
                                    <strong class="profit-positive">
                                        {{ number_format($item['utilidad_estimada'], 2) }} Bs
                                    </strong>
                                @endif
                            </td>

                            <td>
                                @if ($item['margen'] < 0)
                                    <span class="badge badge-danger">{{ number_format($item['margen'], 2) }}%</span>
                                @elseif ($item['margen'] < 20)
                                    <span class="badge badge-warning">{{ number_format($item['margen'], 2) }}%</span>
                                @else
                                    <span class="badge badge-success">{{ number_format($item['margen'], 2) }}%</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table-message">
                                No hay ventas completadas en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $utilidadesPaginadas->links() }}
        </div>
    </div>

</div>

@endsection