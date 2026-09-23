@extends('layouts.app')

@section('title', 'Compras | Santo Remedio')
@section('page-title', 'Compras')
@section('page-subtitle', 'Registro de compras a proveedores e ingreso de inventario')

@section('content')

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-bag-plus"></i>
                Compras registradas
            </h2>

            <p>
                Controle compras, pagos a proveedores, saldos pendientes e ingreso automático a inventario.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('registrar_compra'))
            <a href="{{ route('compras.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Nueva compra
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('compras.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar compra</label>
            <input
                type="text"
                name="busqueda"
                value="{{ $busqueda }}"
                placeholder="N° compra o proveedor"
            >
        </div>

        <div class="form-group">
            <label>Estado de pago</label>
            <select name="estado_pago">
                <option value="">Todos</option>
                <option value="pagado" @selected($estadoPago === 'pagado')>Pagado</option>
                <option value="parcial" @selected($estadoPago === 'parcial')>Parcial</option>
                <option value="pendiente" @selected($estadoPago === 'pendiente')>Pendiente</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('compras.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table purchases-table">
            <thead>
                <tr>
                    <th>N° compra</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Sucursal</th>
                    <th>Total</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                    <th>Estado pago</th>
                    <th class="table-actions-cell">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($compras as $compra)
                    <tr>
                        <td>
                            <strong>{{ $compra->numero_compra }}</strong>

                            @if ($compra->estado === 'anulada')
                                <br>
                                <span class="badge badge-danger">Anulada</span>
                            @endif
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
                            <strong>{{ number_format($compra->total, 2) }} Bs</strong>
                        </td>

                        <td>
                            {{ number_format($compra->monto_pagado, 2) }} Bs
                        </td>

                        <td>
                            @if ($compra->saldo_pendiente > 0)
                                <strong class="purchase-debt">
                                    {{ number_format($compra->saldo_pendiente, 2) }} Bs
                                </strong>
                            @else
                                <strong class="purchase-paid">
                                    0.00 Bs
                                </strong>
                            @endif
                        </td>

                        <td>
                            @if ($compra->estado_pago === 'pagado')
                                <span class="badge badge-success">Pagado</span>
                            @elseif ($compra->estado_pago === 'parcial')
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

                                @if ($compra->saldo_pendiente > 0 && $compra->estado !== 'anulada' && auth()->user()->tienePermiso('pagar_compra'))
                                    <a
                                        href="{{ route('compras.pago.create', $compra) }}"
                                        class="icon-action icon-action-edit"
                                        title="Registrar pago"
                                    >
                                        <i class="bi bi-cash-coin"></i>
                                    </a>
                                @endif

                                @if ($compra->estado !== 'anulada' && auth()->user()->tienePermiso('anular_compra'))
                                    <a
                                        href="{{ route('compras.anular.create', $compra) }}"
                                        class="icon-action icon-action-danger"
                                        title="Anular compra"
                                    >
                                        <i class="bi bi-x-octagon"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-table-message">
                            No hay compras registradas.
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

@endsection