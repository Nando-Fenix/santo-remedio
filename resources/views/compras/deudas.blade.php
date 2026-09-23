@extends('layouts.app')

@section('title', 'Deudas con proveedores | Santo Remedio')
@section('page-title', 'Deudas con proveedores')
@section('page-subtitle', 'Compras pendientes o parcialmente pagadas')

@section('content')

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-exclamation-circle"></i>
                Deudas pendientes
            </h2>

            <p>
                Controle compras a crédito o con pagos parciales.
            </p>
        </div>

        <a href="{{ route('compras.index') }}" class="btn-secondary">
            <i class="bi bi-bag-check"></i>
            Ver compras
        </a>
    </div>

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Total deuda</span>
            <strong>{{ number_format($totalDeuda, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Compras con deuda</span>
            <strong>{{ $cantidadDeudas }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Proveedores con deuda</span>
            <strong>{{ $proveedoresConDeuda }}</strong>
        </div>
    </div>

    <form method="GET" action="{{ route('compras.deudas') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar deuda</label>
            <input
                type="text"
                name="busqueda"
                value="{{ $busqueda }}"
                placeholder="N° compra o proveedor"
            >
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('compras.deudas') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Listado de deudas
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table supplier-debts-table">
                <thead>
                    <tr>
                        <th>N° compra</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Sucursal</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($compras as $compra)
                        <tr>
                            <td>
                                <strong>{{ $compra->numero_compra }}</strong>
                            </td>

                            <td>
                                {{ $compra->fecha_compra->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $compra->fecha_compra->format('H:i') }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $compra->proveedor->nombre ?? '-' }}</strong>
                            </td>

                            <td>
                                {{ $compra->sucursal->nombre ?? '-' }}
                            </td>

                            <td>
                                {{ number_format($compra->total, 2) }} Bs
                            </td>

                            <td>
                                {{ number_format($compra->monto_pagado, 2) }} Bs
                            </td>

                            <td>
                                <strong class="supplier-debt-amount">
                                    {{ number_format($compra->saldo_pendiente, 2) }} Bs
                                </strong>
                            </td>

                            <td>
                                @if ($compra->estado_pago === 'parcial')
                                    <span class="badge badge-warning">Parcial</span>
                                @else
                                    <span class="badge badge-danger">Pendiente</span>
                                @endif
                            </td>

                            <td>
                                <div class="action-group">
                                    @if (auth()->user()->tienePermiso('ver_compras'))
                                        <a
                                            href="{{ route('compras.show', $compra) }}"
                                            class="icon-action icon-action-primary"
                                            title="Ver compra"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif

                                    @if (auth()->user()->tienePermiso('pagar_compra'))
                                        <a
                                            href="{{ route('compras.pago.create', $compra) }}"
                                            class="icon-action icon-action-edit"
                                            title="Registrar pago"
                                        >
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-table-message">
                                No hay deudas pendientes con proveedores.
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