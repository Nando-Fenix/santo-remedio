@extends('layouts.app')

@section('title', 'Deudas con proveedores | Santo Remedio')
@section('page-title', 'Deudas con proveedores')
@section('page-subtitle', 'Compras pendientes o parcialmente pagadas')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Deudas pendientes</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Controle compras a crédito o con pagos parciales.
            </p>
        </div>

        <a href="{{ route('compras.index') }}" class="btn-secondary">
            Ver compras
        </a>
    </div>

    <form method="GET" action="{{ route('compras.deudas') }}" style="margin-top: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar deuda</label>
                <input
                    type="text"
                    name="busqueda"
                    value="{{ $busqueda }}"
                    placeholder="N° compra o proveedor"
                >
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('compras.deudas') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid">
    <div class="stat-card">
        <span>Total deuda</span>
        <h3>{{ number_format($totalDeuda, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Compras con deuda</span>
        <h3>{{ $cantidadDeudas }}</h3>
    </div>

    <div class="stat-card">
        <span>Proveedores con deuda</span>
        <h3>{{ $proveedoresConDeuda }}</h3>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Listado de deudas</h3>

    <div class="table-container">
        <table class="table">
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
                    <th style="width: 190px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $compra)
                    <tr>
                        <td><strong>{{ $compra->numero_compra }}</strong></td>
                        <td>{{ $compra->fecha_compra->format('d/m/Y H:i') }}</td>
                        <td>{{ $compra->proveedor->nombre ?? '-' }}</td>
                        <td>{{ $compra->sucursal->nombre ?? '-' }}</td>
                        <td>{{ number_format($compra->total, 2) }} Bs</td>
                        <td>{{ number_format($compra->monto_pagado, 2) }} Bs</td>
                        <td>
                            <strong>{{ number_format($compra->saldo_pendiente, 2) }} Bs</strong>
                        </td>
                        <td>
                            @if ($compra->estado_pago === 'parcial')
                                <span class="badge badge-warning">Parcial</span>
                            @else
                                <span class="badge badge-danger">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                                    Ver
                                </a>

                                <a href="{{ route('compras.pago.create', $compra) }}" class="btn-primary">
                                    Pagar
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6B7280;">
                            No hay deudas pendientes con proveedores.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $compras->links() }}
    </div>
</div>

@endsection