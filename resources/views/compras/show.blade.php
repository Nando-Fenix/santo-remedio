@extends('layouts.app')

@section('title', 'Detalle de compra | Santo Remedio')
@section('page-title', 'Detalle de compra')
@section('page-subtitle', 'Información de compra, productos ingresados y pagos registrados')

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
                <i class="bi bi-bag-check"></i>
                Compra {{ $compra->numero_compra }}
            </h2>

            <p>
                Registrada el {{ $compra->fecha_compra->format('d/m/Y H:i') }}
                ·
                Proveedor: <strong>{{ $compra->proveedor->nombre ?? '-' }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            @if ($compra->estado === 'anulada')
                <span class="badge badge-danger">Anulada</span>
            @elseif ($compra->estado_pago === 'pagado')
                <span class="badge badge-success">Pagado</span>
            @elseif ($compra->estado_pago === 'parcial')
                <span class="badge badge-warning">Parcial</span>
            @else
                <span class="badge badge-danger">Pendiente</span>
            @endif

            @if ($compra->saldo_pendiente > 0 && $compra->estado === 'registrada' && auth()->user()->tienePermiso('pagar_compra'))
                <a href="{{ route('compras.pago.create', $compra) }}" class="btn-primary">
                    <i class="bi bi-cash-coin"></i>
                    Pagar
                </a>
            @endif

            @if ($compra->estado === 'registrada' && auth()->user()->tienePermiso('anular_compra'))
                <a href="{{ route('compras.anular.create', $compra) }}" class="btn-danger">
                    <i class="bi bi-x-octagon"></i>
                    Anular
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_compras'))
                <a href="{{ route('compras.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>

    @if ($compra->estado === 'anulada')
        <div class="alert-danger" style="margin-bottom: 12px;">
            Esta compra fue anulada. No cuenta como deuda activa ni como compra vigente.
        </div>
    @endif

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Total compra</span>
            <strong>{{ number_format($compra->total, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Pagado</span>
            <strong>{{ number_format($compra->monto_pagado, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Saldo pendiente</span>
            <strong>{{ number_format($compra->saldo_pendiente, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Estado de pago</span>
            <strong>
                @if ($compra->estado_pago === 'pagado')
                    Pagado
                @elseif ($compra->estado_pago === 'parcial')
                    Parcial
                @else
                    Pendiente
                @endif
            </strong>
        </div>
    </div>

    <div class="purchase-show-mini-info">
        <div>
            <span>Proveedor</span>
            <strong>{{ $compra->proveedor->nombre ?? '-' }}</strong>
        </div>

        <div>
            <span>Sucursal</span>
            <strong>{{ $compra->sucursal->nombre ?? '-' }}</strong>
        </div>

        <div>
            <span>Registrado por</span>
            <strong>{{ $compra->usuario->nombre ?? '-' }}</strong>
        </div>

        <div>
            <span>Tipo de pago</span>
            <strong>{{ ucfirst($compra->tipo_pago) }}</strong>
        </div>

        <div>
            <span>Subtotal</span>
            <strong>{{ number_format($compra->subtotal, 2) }} Bs</strong>
        </div>

        <div>
            <span>Descuento</span>
            <strong>{{ number_format($compra->descuento_total, 2) }} Bs</strong>
        </div>
    </div>

    @if ($compra->observacion)
        <div class="purchase-observation-box">
            <span>Observación</span>
            <strong>{{ $compra->observacion }}</strong>
        </div>
    @endif

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-box-seam"></i>
                Productos ingresados
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table purchase-show-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Forma</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Cant.</th>
                        <th>Ingresa</th>
                        <th>P. compra</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($compra->detalles as $detalle)
                        <tr>
                            <td>
                                <strong>
                                    {{ $detalle->productoPresentacion->nombre_mostrado ?? $detalle->producto->nombre_comercial ?? '-' }}
                                </strong>

                                @if ($detalle->producto?->laboratorio || $detalle->producto?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        @if ($detalle->producto?->laboratorio)
                                            {{ $detalle->producto->laboratorio->nombre }}
                                        @endif

                                        @if ($detalle->producto?->laboratorio && $detalle->producto?->concentracion)
                                            |
                                        @endif

                                        @if ($detalle->producto?->concentracion)
                                            {{ $detalle->producto->concentracion }}
                                        @endif
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $detalle->productoPresentacion->presentacion->nombre ?? '-' }}

                                @if ($detalle->productoPresentacion?->unidades_equivalentes)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $detalle->productoPresentacion->unidades_equivalentes }} unidad(es)
                                    </small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $detalle->lote->numero_lote ?? 'Sin lote' }}</strong>
                            </td>

                            <td>
                                {{ $detalle->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td>
                                <strong>{{ $detalle->cantidad }}</strong>
                            </td>

                            <td>
                                <strong>{{ $detalle->unidades_ingresadas }}</strong>
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $detalle->cantidad }} x {{ $detalle->productoPresentacion->unidades_equivalentes ?? 1 }}
                                </small>
                            </td>

                            <td>
                                {{ number_format($detalle->precio_compra, 2) }} Bs
                            </td>

                            <td>
                                <strong>{{ number_format($detalle->subtotal, 2) }} Bs</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-message">
                                No hay productos en esta compra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-cash-coin"></i>
                Pagos registrados
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table purchase-payments-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Método</th>
                        <th>Monto</th>
                        <th>Registrado por</th>
                        <th>Observación</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($compra->pagos as $pago)
                        <tr>
                            <td>
                                {{ $pago->fecha_pago->format('d/m/Y') }}
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $pago->fecha_pago->format('H:i') }}
                                </small>
                            </td>

                            <td>{{ ucfirst($pago->metodo_pago) }}</td>

                            <td>
                                <strong>{{ number_format($pago->monto, 2) }} Bs</strong>
                            </td>

                            <td>{{ $pago->usuario->nombre ?? '-' }}</td>

                            <td>
                                <span class="purchase-payment-observation">
                                    {{ $pago->observacion ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-table-message">
                                No hay pagos registrados para esta compra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection