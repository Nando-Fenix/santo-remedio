@extends('layouts.app')

@section('title', 'Detalle de compra | Santo Remedio')
@section('page-title', 'Detalle de compra')
@section('page-subtitle', 'Información de compra, productos ingresados y pagos registrados')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Compra {{ $compra->numero_compra }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Registrada el {{ $compra->fecha_compra->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if ($compra->estado === 'registrada' && auth()->user()->tienePermiso('anular_compra'))
                <a href="{{ route('compras.anular.create', $compra) }}" class="btn-danger">
                    Anular compra
                </a>
            @endif

            @if ($compra->saldo_pendiente > 0 && $compra->estado === 'registrada' && auth()->user()->tienePermiso('pagar_compra'))
                <a href="{{ route('compras.pago.create', $compra) }}" class="btn-primary">
                    Registrar pago
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_compras'))
                <a href="{{ route('compras.index') }}" class="btn-secondary">
                    Volver
                </a>
            @endif
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Proveedor</span>
            <h3>{{ $compra->proveedor->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Sucursal</span>
            <h3>{{ $compra->sucursal->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Registrado por</span>
            <h3>{{ $compra->usuario->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Tipo de pago</span>
            <h3>{{ ucfirst($compra->tipo_pago) }}</h3>
        </div>
    </div>

    @if ($compra->observacion)
        <p style="margin-top: 18px;">
            <strong>Observación:</strong> {{ $compra->observacion }}
        </p>
    @endif
</div>

@if ($compra->estado === 'anulada')
    <div class="alert-danger" style="margin-top: 14px;">
        Esta compra fue anulada. No cuenta como deuda activa ni como compra vigente.
        @if ($compra->observacion)
            <br>
            <strong>Observación:</strong> {{ $compra->observacion }}
        @endif
    </div>
@endif

<div class="grid">
    <div class="stat-card">
        <span>Subtotal</span>
        <h3>{{ number_format($compra->subtotal, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Descuento</span>
        <h3>{{ number_format($compra->descuento_total, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Total compra</span>
        <h3>{{ number_format($compra->total, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Saldo pendiente</span>
        <h3>{{ number_format($compra->saldo_pendiente, 2) }} Bs</h3>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos ingresados</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto comprado</th>
                    <th>Forma</th>
                    <th>Lote</th>
                    <th>Vencimiento</th>
                    <th>Cantidad comprada</th>
                    <th>Ingresa al inventario</th>
                    <th>Precio compra</th>
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
                                <small style="color: #6B7280;">
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
                                <small style="color: #6B7280;">
                                    {{ $detalle->productoPresentacion->unidades_equivalentes }} unidad(es) por cantidad
                                </small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $detalle->lote->numero_lote ?? 'Sin lote' }}</strong>
                        </td>

                        <td>
                            @if ($detalle->lote?->fecha_vencimiento)
                                {{ $detalle->lote->fecha_vencimiento->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>
                            <strong>{{ $detalle->unidades_ingresadas }}</strong>
                            <br>
                            <small style="color: #6B7280;">
                                {{ $detalle->cantidad }} x {{ $detalle->productoPresentacion->unidades_equivalentes ?? 1 }}
                            </small>
                        </td>
                        <td>{{ number_format($detalle->precio_compra, 2) }} Bs</td>
                        <td>{{ number_format($detalle->subtotal, 2) }} Bs</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #6B7280;">
                            No hay productos en esta compra.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Pagos registrados</h3>

    <div class="table-container">
        <table class="table">
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
                        <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                        <td>{{ ucfirst($pago->metodo_pago) }}</td>
                        <td>{{ number_format($pago->monto, 2) }} Bs</td>
                        <td>{{ $pago->usuario->nombre ?? '-' }}</td>
                        <td>{{ $pago->observacion ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6B7280;">
                            No hay pagos registrados para esta compra.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection