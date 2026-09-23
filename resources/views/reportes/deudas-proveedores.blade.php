@extends('layouts.app')

@section('title', 'Deudas a proveedores | Santo Remedio')
@section('page-title', 'Deudas a proveedores')
@section('page-subtitle', 'Control de compras pendientes de pago')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-exclamation-circle"></i>
                Deudas a proveedores
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
                |
                Compras pendientes de pago agrupadas por proveedor.
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.deudas-proveedores.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.deudas-proveedores.exportar-csv', request()->query()) }}"
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
        <form method="GET" action="{{ route('reportes.deudas-proveedores') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="form-group filter-search">
                <label>Proveedor</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Nombre, NIT o teléfono"
                >
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.deudas-proveedores') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat report-mini-warning">
            <span>Compras pendientes</span>
            <strong>{{ $resumen['cantidad_deudas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Total comprado</span>
            <strong>{{ number_format($resumen['total_comprado'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Total pagado</span>
            <strong>{{ number_format($resumen['total_pagado'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Saldo pendiente</span>
            <strong>{{ number_format($resumen['total_deuda'], 2) }} Bs</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-people"></i>
                Resumen por proveedor
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-provider-debts-table">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>NIT</th>
                        <th>Teléfono</th>
                        <th>Compras</th>
                        <th>Total comprado</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($deudasPorProveedor as $deuda)
                        <tr>
                            <td>
                                <strong>{{ $deuda['proveedor']->nombre ?? '-' }}</strong>
                            </td>

                            <td>
                                {{ $deuda['proveedor']->nit ?? '-' }}
                            </td>

                            <td>
                                {{ $deuda['proveedor']->telefono ?? '-' }}
                            </td>

                            <td>
                                <span class="reorder-suggested">
                                    {{ $deuda['cantidad_compras'] }}
                                </span>
                            </td>

                            <td>
                                {{ number_format($deuda['total_comprado'], 2) }} Bs
                            </td>

                            <td>
                                {{ number_format($deuda['total_pagado'], 2) }} Bs
                            </td>

                            <td>
                                <strong class="provider-debt-money">
                                    {{ number_format($deuda['saldo_pendiente'], 2) }} Bs
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table-message">
                                No hay deudas pendientes en este rango.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle de compras pendientes
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table report-provider-purchases-table">
                <thead>
                    <tr>
                        <th>N° compra</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
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
                                <strong class="provider-debt-money">
                                    {{ number_format($compra->saldo_pendiente, 2) }} Bs
                                </strong>
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
                            <td colspan="7" class="empty-table-message">
                                No hay compras pendientes en este rango.
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