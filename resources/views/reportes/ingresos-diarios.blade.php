@extends('layouts.app')

@section('title', 'Ingresos diarios | Santo Remedio')
@section('page-title', 'Ingresos diarios')
@section('page-subtitle', 'Resumen general de ventas y servicios de farmacia')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-calendar-check"></i>
                Ingresos diarios
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
                href="{{ route('reportes.ingresos-diarios.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.ingresos-diarios.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.ingresos-diarios') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.ingresos-diarios') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="cash-report-main-summary">
        <div class="cash-report-total-card">
            <span>Ventas completadas</span>
            <strong>{{ number_format($resumen['total_ventas'], 2) }} Bs</strong>
            <small>{{ $resumen['cantidad_ventas'] }} venta(s) registradas</small>
        </div>

        <div class="cash-report-total-card">
            <span>Servicios completados</span>
            <strong>{{ number_format($resumen['total_servicios'], 2) }} Bs</strong>
            <small>{{ $resumen['cantidad_servicios'] }} atención(es) de servicio</small>
        </div>

        <div class="cash-report-total-card cash-report-main-total">
            <span>Ingresos totales</span>
            <strong>{{ number_format($resumen['ingresos_totales'], 2) }} Bs</strong>
            <small>Ventas + servicios completados</small>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Cantidad de ventas</span>
            <strong>{{ $resumen['cantidad_ventas'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Atenciones de servicio</span>
            <strong>{{ $resumen['cantidad_servicios'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Ventas anuladas</span>
            <strong>{{ $resumen['ventas_anuladas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Servicios anulados</span>
            <strong>{{ $resumen['servicios_anulados'] }}</strong>
        </div>
    </div>

    <div class="report-summary-strip report-summary-two">
        <div class="report-mini-stat report-mini-danger">
            <span>Monto ventas anuladas</span>
            <strong>{{ number_format($resumen['monto_ventas_anuladas'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Monto servicios anulados</span>
            <strong>{{ number_format($resumen['monto_servicios_anulados'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <div>
                <h3>
                    <i class="bi bi-list-check"></i>
                    Detalle de servicios registrados
                </h3>

                <small style="color:#6B7280;">
                    Las ventas completas se revisan desde el reporte de ventas.
                </small>
            </div>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-daily-income-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Servicio</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($detalleServicios as $atencion)
                        <tr>
                            <td>
                                {{ $atencion->fecha_hora->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $atencion->fecha_hora->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $atencion->servicio->nombre ?? '-' }}</strong>
                            </td>

                            <td>
                                {{ $atencion->cliente->nombre ?? 'Consumidor final' }}
                            </td>

                            <td>
                                <span class="badge badge-soft">
                                    {{ $atencion->metodoPago->nombre ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($atencion->total, 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                @if ($atencion->estado === 'completada')
                                    <span class="badge badge-success">Completada</span>
                                @else
                                    <span class="badge badge-danger">Anulada</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    <a
                                        href="{{ route('atenciones-servicio.show', $atencion) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver atención"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table-message">
                                No hay servicios registrados en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $detalleServicios->links() }}
        </div>
    </div>

</div>

@endsection