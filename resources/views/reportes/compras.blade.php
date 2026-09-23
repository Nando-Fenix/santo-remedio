@extends('layouts.app')

@section('title', 'Reporte de compras | Santo Remedio')
@section('page-title', 'Reporte de compras')
@section('page-subtitle', 'Resumen y detalle de compras por sucursal')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-bag-check"></i>
                Reporte de compras
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
                href="{{ route('reportes.compras.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.compras.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.compras') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente" @selected($estado === 'pendiente')>Pendientes</option>
                    <option value="pagada" @selected($estado === 'pagada')>Pagadas</option>
                    <option value="anulada" @selected($estado === 'anulada')>Anuladas</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.compras') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Compras registradas</span>
            <strong>{{ $resumen['cantidad_compras'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Pagadas</span>
            <strong>{{ $resumen['compras_pagadas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-warning">
            <span>Pendientes</span>
            <strong>{{ $resumen['compras_pendientes'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Anuladas</span>
            <strong>{{ $resumen['compras_anuladas'] }}</strong>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat report-mini-total">
            <span>Total comprado</span>
            <strong>{{ number_format($resumen['total_comprado'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Total pagado</span>
            <strong>{{ number_format($resumen['total_pagado'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-warning">
            <span>Saldo pendiente</span>
            <strong>{{ number_format($resumen['saldo_pendiente'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Total anulado</span>
            <strong>{{ number_format($resumen['total_anulado'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle de compras
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-purchases-table">
                <thead>
                    <tr>
                        <th>N° compra</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($compras as $compra)
                        <tr>
                            <td>
                                <strong>{{ $compra->numero_compra ?? $compra->id }}</strong>
                            </td>

                            <td>
                                {{ $compra->fecha_compra?->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $compra->fecha_compra?->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                {{ $compra->proveedor->nombre ?? '-' }}
                            </td>

                            <td>
                                <strong class="report-money">
                                    {{ number_format($compra->total, 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                {{ number_format($compra->monto_pagado, 2) }} Bs
                            </td>

                            <td>
                                @if ($compra->saldo_pendiente > 0)
                                    <strong class="purchase-pending-money">
                                        {{ number_format($compra->saldo_pendiente, 2) }} Bs
                                    </strong>
                                @else
                                    <span class="purchase-paid-money">
                                        {{ number_format($compra->saldo_pendiente, 2) }} Bs
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($compra->estado === 'pagada')
                                    <span class="badge badge-success">Pagada</span>
                                @elseif ($compra->estado === 'pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                @else
                                    <span class="badge badge-danger">Anulada</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    <a
                                        href="{{ route('compras.show', $compra) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver compra"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay compras registradas en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $compras->links() }}
        </div>
    </div>

</div>

@endsection