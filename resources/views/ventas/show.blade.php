@extends('layouts.app')

@section('title', 'Detalle de venta | Santo Remedio')
@section('page-title', 'Detalle de venta')
@section('page-subtitle', 'Comprobante interno de venta')

@section('content')

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">
                Venta {{ $venta->numero_venta }}
            </h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Fecha: {{ $venta->fecha_hora->format('d/m/Y H:i') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if ($venta->estado === 'completada' && $venta->caja?->estado === 'abierta')

                @if (auth()->user()->tienePermiso('reembolsar_venta'))
                    <a href="{{ route('reembolsos.create', $venta) }}" class="btn-primary">
                        Registrar reembolso
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('cambiar_producto'))
                    <a href="{{ route('cambios-producto.create', $venta) }}" class="btn-primary">
                        Cambio de producto
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('anular_venta'))
                    <a href="{{ route('ventas.anular.create', $venta) }}" class="btn-danger">
                        Anular venta
                    </a>
                @endif

            @endif

            <a href="{{ route('ventas.index') }}" class="btn-secondary">
                Volver a ventas
            </a>
        </div>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <span>Sucursal</span>
        <h3 style="font-size: 18px;">{{ $venta->sucursal->nombre ?? '-' }}</h3>
    </div>

    <div class="stat-card">
        <span>Vendedor</span>
        <h3 style="font-size: 18px;">{{ $venta->usuario->nombre ?? '-' }}</h3>
    </div>

    <div class="stat-card">
        <span>Total</span>
        <h3>{{ number_format($venta->total, 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Cambio</span>
        <h3>{{ number_format($venta->cambio, 2) }} Bs</h3>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos vendidos</h3>

    <div class="table-container">
        <p>
            <strong>Cliente:</strong>
            {{ $venta->cliente->nombre ?? 'Consumidor final' }}

            @if ($venta->cliente?->ci_nit)
                <br>
                <strong>CI/NIT:</strong> {{ $venta->cliente->ci_nit }}
            @endif

            @if ($venta->cliente?->telefono)
                <br>
                <strong>Teléfono:</strong> {{ $venta->cliente->telefono }}
            @endif
        </p>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Presentación</th>
                    <th>Lote</th>
                    <th>Cantidad</th>
                    <th>Unidades descontadas</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->detalles as $detalle)
                    <tr>
                        <td>
                            {{ $detalle->producto->nombre_comercial ?? '-' }}
                            @if($detalle->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">{{ $detalle->producto->concentracion }}</small>
                            @endif
                        </td>
                        <td>{{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}</td>
                        <td>
                            @forelse ($detalle->lotesDescontados as $loteDescontado)
                                <div>
                                    {{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}
                                    <small style="color: #6B7280;">
                                        ({{ $loteDescontado->unidades_descontadas }} unidades)
                                    </small>
                                </div>
                            @empty
                                Sin lote
                            @endforelse
                        </td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>{{ $detalle->unidades_descontadas }}</td>
                        <td>{{ number_format($detalle->precio_unitario, 2) }} Bs</td>
                        <td>{{ number_format($detalle->subtotal, 2) }} Bs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #4C1D95;">Pago y recibo</h3>

    <div class="table-container">
        <table class="table">
            <tbody>
                <tr>
                    <th>Subtotal</th>
                    <td>{{ number_format($venta->subtotal, 2) }} Bs</td>
                </tr>
                <tr>
                    <th>Descuento</th>
                    <td>{{ number_format($venta->descuento_total, 2) }} Bs</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td><strong>{{ number_format($venta->total, 2) }} Bs</strong></td>
                </tr>
                <tr>
                    <th>Monto recibido</th>
                    <td>{{ number_format($venta->monto_recibido, 2) }} Bs</td>
                </tr>
                <tr>
                    <th>Cambio</th>
                    <td>{{ number_format($venta->cambio, 2) }} Bs</td>
                </tr>
                <tr>
                    <th>Método de pago</th>
                    <td>
                        @foreach ($venta->pagos as $pago)
                            <span class="badge badge-soft">
                                {{ $pago->metodoPago->nombre ?? '-' }}:
                                {{ number_format($pago->monto, 2) }} Bs
                            </span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>N° recibo</th>
                    <td>{{ $venta->recibo->numero_recibo ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td>
                        <span class="badge badge-success">
                            {{ ucfirst($venta->estado) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($venta->observacion)
        <p style="margin-top: 18px; color: #6B7280;">
            <strong>Observación:</strong> {{ $venta->observacion }}
        </p>
    @endif
</div>
@if ($venta->reembolsos->count() > 0)
    <div class="card" style="margin-top: 22px;">
        <h3 style="margin-top: 0; color: #4C1D95;">Reembolsos registrados</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° reembolso</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->reembolsos as $reembolso)
                        <tr>
                            <td><strong>{{ $reembolso->numero_reembolso }}</strong></td>
                            <td>{{ $reembolso->fecha_reembolso->format('d/m/Y H:i') }}</td>
                            <td>{{ number_format($reembolso->monto_total, 2) }} Bs</td>
                            <td>{{ $reembolso->motivo }}</td>
                            <td>
                                @if ($reembolso->estado === 'registrado')
                                    <span class="badge badge-success">Registrado</span>
                                @else
                                    <span class="badge badge-danger">Anulado</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reembolsos.show', $reembolso) }}" class="btn-secondary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if ($venta->cambiosProducto->count() > 0)
    <div class="card" style="margin-top: 22px;">
        <h3 style="margin-top: 0; color: #4C1D95;">Cambios de producto registrados</h3>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° cambio</th>
                        <th>Fecha</th>
                        <th>Producto devuelto</th>
                        <th>Producto nuevo</th>
                        <th>Diferencia</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->cambiosProducto as $cambio)
                        @php
                            $detalleCambio = $cambio->detalles->first();
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $cambio->numero_cambio }}</strong>
                            </td>

                            <td>
                                {{ $cambio->fecha_cambio->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                @if ($detalleCambio)
                                    <strong>{{ $detalleCambio->productoDevuelto->nombre_comercial ?? '-' }}</strong>
                                    <br>
                                    <small style="color: #6B7280;">
                                        Cantidad: {{ $detalleCambio->cantidad_devuelta }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if ($detalleCambio)
                                    <strong>{{ $detalleCambio->productoNuevo->nombre_comercial ?? '-' }}</strong>
                                    <br>
                                    <small style="color: #6B7280;">
                                        Cantidad: {{ $detalleCambio->cantidad_nueva }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{ number_format($cambio->diferencia, 2) }} Bs
                            </td>

                            <td>
                                @if ($cambio->tipo_diferencia === 'cliente_paga')
                                    <span class="badge badge-success">Cliente pagó</span>
                                @elseif ($cambio->tipo_diferencia === 'farmacia_devuelve')
                                    <span class="badge badge-warning">Farmacia devolvió</span>
                                @else
                                    <span class="badge badge-secondary">Sin diferencia</span>
                                @endif
                            </td>

                            <td>{{ $cambio->motivo }}</td>

                            <td>
                                <a href="{{ route('cambios-producto.show', $cambio) }}" class="btn-secondary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection