@extends('layouts.app')

@section('title', 'Compras | Santo Remedio')
@section('page-title', 'Compras')
@section('page-subtitle', 'Registro de compras a proveedores e ingreso de inventario')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Compras registradas</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Controle compras, pagos a proveedores, saldos pendientes e ingreso automático a inventario.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('registrar_compra'))
            <a href="{{ route('compras.create') }}" class="btn-primary">
                Nueva compra
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('compras.index') }}" style="margin-top: 18px;">
        <div class="form-grid">
            <div class="form-group">
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
                    <option value="pagado" {{ $estadoPago === 'pagado' ? 'selected' : '' }}>Pagado</option>
                    <option value="parcial" {{ $estadoPago === 'parcial' ? 'selected' : '' }}>Parcial</option>
                    <option value="pendiente" {{ $estadoPago === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('compras.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="card">
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
                    <th>Estado pago</th>
                    <th>Acciones</th>
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
                        <td>{{ number_format($compra->saldo_pendiente, 2) }} Bs</td>
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
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @if (auth()->user()->tienePermiso('ver_compras'))
                                    <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                                        Ver
                                    </a>
                                @endif

                                @if ($compra->saldo_pendiente > 0 && $compra->estado !== 'anulada' && auth()->user()->tienePermiso('pagar_compra'))
                                    <a href="{{ route('compras.pago.create', $compra) }}" class="btn-primary">
                                        Pagar
                                    </a>
                                @endif

                                @if ($compra->estado !== 'anulada' && auth()->user()->tienePermiso('anular_compra'))
                                    <a href="{{ route('compras.anular.create', $compra) }}" class="btn-danger">
                                        Anular
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #6B7280;">
                            No hay compras registradas.
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