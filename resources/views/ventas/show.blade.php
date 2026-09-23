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

@if (session('error'))
    <div class="alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-receipt"></i>
                Venta {{ $venta->numero_venta }}
            </h2>

            <p>
                {{ $venta->fecha_hora->format('d/m/Y H:i') }}
                ·
                Cliente: <strong>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            <span class="badge {{ $venta->estado === 'completada' ? 'badge-success' : 'badge-warning' }}">
                {{ ucfirst(str_replace('_', ' ', $venta->estado)) }}
            </span>

            <a href="{{ route('ventas.recibo', $venta) }}" class="btn-primary" onclick="abrirModalImpresion('{{ route('ventas.recibo', $venta) }}')">
                <i class="bi bi-printer"></i>
                Imprimir
            </a>

            @if ($venta->estado === 'completada' && $venta->caja?->estado === 'abierta')
                @if (auth()->user()->tienePermiso('reembolsar_venta'))
                    @if ($venta->promociones->count() > 0)
                        <button type="button" class="btn-secondary" onclick="Swal.fire({
                            icon: 'info',
                            title: 'Venta con promoción',
                            text: 'Esta venta contiene promociones. Para evitar errores de stock y precio promocional, debe anularse la venta completa.',
                            confirmButtonColor: '#6D28D9'
                        })">
                            <i class="bi bi-cash-coin"></i>
                            Reembolso
                        </button>
                    @else
                        <a href="{{ route('reembolsos.create', $venta) }}" class="btn-secondary">
                            <i class="bi bi-cash-coin"></i>
                            Reembolso
                        </a>
                    @endif
                @endif

                @if (auth()->user()->tienePermiso('cambiar_producto'))
                    @if ($venta->promociones->count() > 0)
                        <button type="button" class="btn-secondary" onclick="Swal.fire({
                            icon: 'info',
                            title: 'Venta con promoción',
                            text: 'Esta venta contiene promociones. Para evitar errores, no se permite cambio parcial. Debe anularse la venta completa.',
                            confirmButtonColor: '#6D28D9'
                        })">
                            <i class="bi bi-arrow-left-right"></i>
                            Cambio
                        </button>
                    @else
                        <a href="{{ route('cambios-producto.create', $venta) }}" class="btn-secondary">
                            <i class="bi bi-arrow-left-right"></i>
                            Cambio
                        </a>
                    @endif
                @endif

                @if (auth()->user()->tienePermiso('anular_venta'))
                    <a href="{{ route('ventas.anular.create', $venta) }}" class="btn-danger">
                        <i class="bi bi-x-octagon"></i>
                        Anular
                    </a>
                @endif
            @endif

            @if (auth()->user()->tienePermiso('ver_ventas'))
                <a href="{{ route('ventas.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Total</span>
            <strong>{{ number_format($venta->total, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Subtotal</span>
            <strong>{{ number_format($venta->subtotal, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Descuento</span>
            <strong>{{ number_format($venta->descuento_total, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Cambio</span>
            <strong>{{ number_format($venta->cambio, 2) }} Bs</strong>
        </div>
    </div>

    <div class="sale-show-mini-info">
        <div>
            <span>Sucursal</span>
            <strong>{{ $venta->sucursal->nombre ?? '-' }}</strong>
        </div>

        <div>
            <span>Vendedor</span>
            <strong>{{ $venta->usuario->nombre ?? '-' }}</strong>
        </div>

        <div>
            <span>CI/NIT</span>
            <strong>{{ $venta->cliente->ci_nit ?? '-' }}</strong>
        </div>

        <div>
            <span>Teléfono</span>
            <strong>{{ $venta->cliente->telefono ?? '-' }}</strong>
        </div>

        <div>
            <span>Recibo</span>
            <strong>{{ $venta->recibo->numero_recibo ?? '-' }}</strong>
        </div>

        <div>
            <span>Monto recibido</span>
            <strong>{{ number_format($venta->monto_recibido, 2) }} Bs</strong>
        </div>
    </div>

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-capsule"></i>
                Productos vendidos
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table sale-show-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Forma</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Unidades</th>
                        <th>P. Unit.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($venta->detalles as $detalle)
                        <tr>
                            <td>
                                <strong>
                                    {{ $detalle->productoPresentacion->nombre_mostrado ?? $detalle->producto->nombre_comercial ?? '-' }}
                                </strong>

                                @if($detalle->producto?->laboratorio || $detalle->producto?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        @if($detalle->producto?->laboratorio)
                                            {{ $detalle->producto->laboratorio->nombre }}
                                        @endif

                                        @if($detalle->producto?->laboratorio && $detalle->producto?->concentracion)
                                            |
                                        @endif

                                        @if($detalle->producto?->concentracion)
                                            {{ $detalle->producto->concentracion }}
                                        @endif
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $detalle->productoPresentacion->presentacion->nombre ?? '-' }}

                                @if($detalle->productoPresentacion?->unidades_equivalentes)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $detalle->productoPresentacion->unidades_equivalentes }} unidad(es)
                                    </small>
                                @endif
                            </td>

                            <td>
                                @forelse ($detalle->lotesDescontados as $loteDescontado)
                                    <div class="mini-lot-line">
                                        <strong>{{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}</strong>
                                        <small>
                                            {{ $loteDescontado->unidades_descontadas }} u.

                                            @if($loteDescontado->lote?->fecha_vencimiento)
                                                · Vence: {{ $loteDescontado->lote->fecha_vencimiento->format('d/m/Y') }}
                                            @endif
                                        </small>
                                    </div>
                                @empty
                                    <small style="color:#6B7280;">Sin lote</small>
                                @endforelse
                            </td>

                            <td><strong>{{ $detalle->cantidad }}</strong></td>
                            <td>{{ $detalle->unidades_descontadas }}</td>
                            <td>{{ number_format($detalle->precio_unitario, 2) }} Bs</td>
                            <td><strong>{{ number_format($detalle->subtotal, 2) }} Bs</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table-message">
                                Esta venta no tiene productos individuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($venta->promociones->count() > 0)
        <div class="detail-section-card compact-section">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-tags"></i>
                    Promociones vendidas
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table sale-show-table">
                    <thead>
                        <tr>
                            <th>Promoción</th>
                            <th>Cant.</th>
                            <th>P. Unit.</th>
                            <th>Subtotal</th>
                            <th>Productos descontados</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($venta->promociones as $detallePromo)
                            <tr>
                                <td>
                                    <strong>{{ $detallePromo->promocion->nombre ?? 'Promoción eliminada' }}</strong>

                                    @if ($detallePromo->promocion?->descripcion)
                                        <br>
                                        <small style="color:#6B7280;">
                                            {{ $detallePromo->promocion->descripcion }}
                                        </small>
                                    @endif
                                </td>

                                <td><strong>{{ $detallePromo->cantidad }}</strong></td>
                                <td>{{ number_format($detallePromo->precio_unitario, 2) }} Bs</td>
                                <td><strong>{{ number_format($detallePromo->subtotal, 2) }} Bs</strong></td>

                                <td>
                                    <div class="sale-promo-products">
                                        @foreach ($detallePromo->items as $item)
                                            <div class="sale-promo-product">
                                                <strong>
                                                    {{ $item->productoPresentacion->nombre_mostrado ?? $item->producto->nombre_comercial ?? '-' }}
                                                </strong>

                                                <small>
                                                    Unidades: {{ $item->unidades_descontadas }}

                                                    @if ($item->lote)
                                                        · Lote: {{ $item->lote->numero_lote }}

                                                        @if ($item->lote->fecha_vencimiento)
                                                            · Vence: {{ $item->lote->fecha_vencimiento->format('d/m/Y') }}
                                                        @endif
                                                    @endif
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="sale-payment-strip">
        <div>
            <span>Método de pago</span>
            <strong>
                @forelse ($venta->pagos as $pago)
                    {{ $pago->metodoPago->nombre ?? '-' }}:
                    {{ number_format($pago->monto, 2) }} Bs
                    @if (!$loop->last) · @endif
                @empty
                    -
                @endforelse
            </strong>
        </div>

        <div>
            <span>Estado</span>
            <strong>{{ ucfirst($venta->estado) }}</strong>
        </div>

        @if ($venta->observacion)
            <div>
                <span>Observación</span>
                <strong>{{ $venta->observacion }}</strong>
            </div>
        @endif
    </div>

</div>

@if ($venta->reembolsos->count() > 0)
    <div class="compact-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-cash-coin"></i>
                Reembolsos registrados
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th class="table-actions-cell">Acción</th>
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
                                <a href="{{ route('reembolsos.show', $reembolso) }}" class="icon-action icon-action-primary" title="Ver reembolso">
                                    <i class="bi bi-eye"></i>
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
    <div class="compact-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-arrow-left-right"></i>
                Cambios de producto registrados
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Fecha</th>
                        <th>Devuelto</th>
                        <th>Nuevo</th>
                        <th>Diferencia</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th class="table-actions-cell">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($venta->cambiosProducto as $cambio)
                        @php
                            $detalleCambio = $cambio->detalles->first();
                        @endphp

                        <tr>
                            <td><strong>{{ $cambio->numero_cambio }}</strong></td>
                            <td>{{ $cambio->fecha_cambio->format('d/m/Y H:i') }}</td>

                            <td>
                                @if ($detalleCambio)
                                    <strong>{{ $detalleCambio->productoPresentacionDevuelta->nombre_mostrado ?? $detalleCambio->productoDevuelto->nombre_comercial ?? '-' }}</strong>
                                    <br>
                                    <small style="color:#6B7280;">
                                        Cant: {{ $detalleCambio->cantidad_devuelta }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if ($detalleCambio)
                                    <strong>{{ $detalleCambio->productoPresentacionNueva->nombre_mostrado ?? $detalleCambio->productoNuevo->nombre_comercial ?? '-' }}</strong>
                                    <br>
                                    <small style="color:#6B7280;">
                                        Cant: {{ $detalleCambio->cantidad_nueva }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ number_format($cambio->diferencia, 2) }} Bs</td>

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
                                <a href="{{ route('cambios-producto.show', $cambio) }}" class="icon-action icon-action-primary" title="Ver cambio">
                                    <i class="bi bi-eye"></i>
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