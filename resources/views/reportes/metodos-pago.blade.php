@extends('layouts.app')

@section('title', 'Métodos de pago | Santo Remedio')
@section('page-title', 'Métodos de pago')
@section('page-subtitle', 'Resumen de ingresos por forma de pago')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-credit-card"></i>
                Métodos de pago
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
                href="{{ route('reportes.metodos-pago.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.metodos-pago.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.metodos-pago') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group">
                <label>Método</label>
                <select name="metodo_pago_id">
                    <option value="">Todos</option>
                    @foreach ($metodosPago as $metodo)
                        <option value="{{ $metodo->id }}" @selected($metodoPagoId == $metodo->id)>
                            {{ $metodo->nombre }} - {{ ucfirst($metodo->tipo) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.metodos-pago') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="cash-report-main-summary">
        <div class="cash-report-total-card">
            <span>Total ventas</span>
            <strong>{{ number_format($resumen['total_ventas'], 2) }} Bs</strong>
            <small>{{ $resumen['cantidad_ventas'] }} venta(s) registradas</small>
        </div>

        <div class="cash-report-total-card">
            <span>Total servicios</span>
            <strong>{{ number_format($resumen['total_servicios'], 2) }} Bs</strong>
            <small>{{ $resumen['cantidad_servicios'] }} servicio(s) registrados</small>
        </div>

        <div class="cash-report-total-card cash-report-main-total">
            <span>Total general</span>
            <strong>{{ number_format($resumen['total_general'], 2) }} Bs</strong>
            <small>Ventas + servicios por método de pago</small>
        </div>
    </div>

    <div class="report-summary-strip report-summary-five">
        <div class="report-mini-stat">
            <span>Ventas</span>
            <strong>{{ number_format($resumen['total_ventas'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Servicios</span>
            <strong>{{ number_format($resumen['total_servicios'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Total general</span>
            <strong>{{ number_format($resumen['total_general'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Cantidad ventas</span>
            <strong>{{ $resumen['cantidad_ventas'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Cantidad servicios</span>
            <strong>{{ $resumen['cantidad_servicios'] }}</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Resumen por método
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-payment-methods-table">
                <thead>
                    <tr>
                        <th>Método</th>
                        <th>Tipo</th>
                        <th>Ventas</th>
                        <th>Total ventas</th>
                        <th>Servicios</th>
                        <th>Total servicios</th>
                        <th>Total general</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($resumenPorMetodo as $item)
                        <tr>
                            <td>
                                <strong>{{ $item['metodo']->nombre ?? '-' }}</strong>
                            </td>

                            <td>
                                <span class="badge badge-soft">
                                    {{ ucfirst($item['metodo']->tipo ?? '-') }}
                                </span>
                            </td>

                            <td>
                                {{ $item['cantidad_ventas'] }}
                            </td>

                            <td>
                                {{ number_format($item['total_ventas'], 2) }} Bs
                            </td>

                            <td>
                                {{ $item['cantidad_servicios'] }}
                            </td>

                            <td>
                                {{ number_format($item['total_servicios'], 2) }} Bs
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($item['total_general'], 2) }} Bs
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table-message">
                                No hay pagos registrados en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection