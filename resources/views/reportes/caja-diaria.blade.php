@extends('layouts.app')

@section('title', 'Reporte de caja diaria | Santo Remedio')
@section('page-title', 'Reporte de caja diaria')
@section('page-subtitle', 'Resumen de ventas, servicios, egresos y anulaciones por caja')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-safe"></i>
                Reporte de caja diaria
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
                |
                Fecha:
                <strong>{{ $fecha }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.caja-diaria.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Exportar
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Reportes
            </a>
        </div>
    </div>

    <div class="compact-card report-filter-card">
        <form method="GET" action="{{ route('reportes.caja-diaria') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Fecha</label>
                <input type="date" name="fecha" value="{{ $fecha }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Ver reporte">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.caja-diaria') }}" class="btn-secondary" title="Hoy">
                    <i class="bi bi-calendar-day"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="cash-report-main-summary">
        <div class="cash-report-total-card">
            <span>Ingresos válidos</span>
            <strong>{{ number_format($totales['ingresos_validos'], 2) }} Bs</strong>
            <small>Ventas completadas + servicios completados</small>
        </div>

        <div class="cash-report-total-card cash-report-danger">
            <span>Egresos</span>
            <strong>{{ number_format($totales['egresos'], 2) }} Bs</strong>
            <small>Salidas registradas en caja</small>
        </div>

        <div class="cash-report-total-card cash-report-warning">
            <span>Anulaciones y reembolsos</span>
            <strong>
                {{ number_format($totales['ventas_anuladas'] + $totales['servicios_anulados'] + $totales['reembolsos'] + $totales['anulaciones'], 2) }} Bs
            </strong>
            <small>Ventas, servicios anulados y devoluciones</small>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Ventas completadas</span>
            <strong>{{ number_format($totales['ventas_completadas'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Servicios completados</span>
            <strong>{{ number_format($totales['servicios_completados'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Ventas anuladas</span>
            <strong>{{ number_format($totales['ventas_anuladas'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Servicios anulados</span>
            <strong>{{ number_format($totales['servicios_anulados'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat report-mini-danger">
            <span>Reembolsos</span>
            <strong>{{ number_format($totales['reembolsos'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Mov. anulación</span>
            <strong>{{ number_format($totales['anulaciones'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Ingresos válidos</span>
            <strong>{{ number_format($totales['ingresos_validos'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-warning">
            <span>Egresos</span>
            <strong>{{ number_format($totales['egresos'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle por caja
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-cash-daily-table">
                <thead>
                    <tr>
                        <th>Caja</th>
                        <th>Sucursal / Turno</th>
                        <th>Estado</th>
                        <th>Inicial</th>
                        <th>Ventas</th>
                        <th>Servicios</th>
                        <th>Egresos</th>
                        <th>Reembolsos</th>
                        <th>Efectivo</th>
                        <th>QR</th>
                        <th>Total final</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($resumenCajas as $item)
                        @php
                            $caja = $item['caja'];
                        @endphp

                        <tr>
                            <td>
                                <strong>#{{ $caja->id }}</strong>
                            </td>

                            <td>
                                <strong>{{ $caja->sucursal->nombre ?? '-' }}</strong>
                                <br>
                                <small style="color:#6B7280;">
                                    Turno: {{ $caja->turno->nombre ?? '-' }}
                                </small>
                            </td>

                            <td>
                                @if ($caja->estado === 'abierta')
                                    <span class="badge badge-success">Abierta</span>
                                @else
                                    <span class="badge badge-danger">Cerrada</span>
                                @endif
                            </td>

                            <td>
                                {{ number_format($caja->monto_inicial, 2) }} Bs
                            </td>

                            <td>
                                <strong class="cash-report-income">
                                    {{ number_format($item['ventas_completadas'], 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                <strong class="cash-report-income">
                                    {{ number_format($item['servicios_completados'], 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                <strong class="cash-report-expense">
                                    {{ number_format($item['egresos'], 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                <strong class="cash-report-expense">
                                    {{ number_format($item['reembolsos'], 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                {{ number_format($caja->total_efectivo, 2) }} Bs
                            </td>

                            <td>
                                {{ number_format($caja->total_qr, 2) }} Bs
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($caja->total_final, 2) }} Bs
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="empty-table-message">
                                No hay cajas registradas en esta fecha.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection