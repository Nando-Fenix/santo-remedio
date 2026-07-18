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
                @if ($venta->promociones->count() > 0)
                    <button type="button" class="btn-primary" onclick="Swal.fire({
                        icon: 'info',
                        title: 'Venta con promoción',
                        text: 'Esta venta contiene promociones. Para evitar errores de stock y precio promocional, debe anularse la venta completa.',
                        confirmButtonColor: '#6D28D9'
                    })">
                        Registrar reembolso
                    </button>
                @else
                    <a href="{{ route('reembolsos.create', $venta) }}" class="btn-primary">
                        Registrar reembolso
                    </a>
                @endif
            @endif

            @if (auth()->user()->tienePermiso('cambiar_producto'))
                @if ($venta->promociones->count() > 0)
                    <button type="button" class="btn-primary" onclick="Swal.fire({
                        icon: 'info',
                        title: 'Venta con promoción',
                        text: 'Esta venta contiene promociones. Para evitar errores, no se permite cambio parcial. Debe anularse la venta completa.',
                        confirmButtonColor: '#6D28D9'
                    })">
                        Cambio de producto
                    </button>
                @else
                    <a href="{{ route('cambios-producto.create', $venta) }}" class="btn-primary">
                        Cambio de producto
                    </a>
                @endif
            @endif

            @if (auth()->user()->tienePermiso('anular_venta'))
                <a href="{{ route('ventas.anular.create', $venta) }}" class="btn-danger">
                    Anular venta
                </a>
            @endif

        @endif

            @if (auth()->user()->tienePermiso('ver_ventas'))
                <a href="{{ route('ventas.index') }}" class="btn-secondary">
                    Volver a ventas
                </a>
            @endif
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
                    <th>Producto vendido</th>
                    <th>Forma</th>
                    <th>Lote descontado</th>
                    <th>Cantidad</th>
                    <th>Unidades descontadas</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->detalles as $detalle)
                    <tr>
                        <td>
                            <strong>{{ $detalle->productoPresentacion->nombre_mostrado ?? $detalle->producto->nombre_comercial ?? '-' }}</strong>

                            @if($detalle->producto?->laboratorio || $detalle->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">
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
                                <small style="color: #6B7280;">
                                    {{ $detalle->productoPresentacion->unidades_equivalentes }} unidad(es) por cantidad
                                </small>
                            @endif
                        </td>
                        <td>
                            @forelse ($detalle->lotesDescontados as $loteDescontado)
                                <div style="margin-bottom: 4px;">
                                    <strong>{{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}</strong>

                                    <br>
                                    <small style="color: #6B7280;">
                                        {{ $loteDescontado->unidades_descontadas }} unidad(es)

                                        @if($loteDescontado->lote?->fecha_vencimiento)
                                            | Vence: {{ $loteDescontado->lote->fecha_vencimiento->format('d/m/Y') }}
                                        @endif
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

        @if ($venta->promociones->count() > 0)
            <div class="card" style="margin-top: 22px;">
                <h3 style="margin-top: 0; color: #4C1D95;">
                    Promociones vendidas
                </h3>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Promoción</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
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
                                            <small style="color: #6B7280;">
                                                {{ $detallePromo->promocion->descripcion }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $detallePromo->cantidad }}
                                    </td>

                                    <td>
                                        {{ number_format($detallePromo->precio_unitario, 2) }} Bs
                                    </td>

                                    <td>
                                        <strong>{{ number_format($detallePromo->subtotal, 2) }} Bs</strong>
                                    </td>

                                    <td>
                                        @foreach ($detallePromo->items as $item)
                                            <div style="margin-bottom: 8px;">
                                                <strong>
                                                    {{ $item->productoPresentacion->nombre_mostrado ?? $item->producto->nombre_comercial ?? '-' }}
                                                </strong>

                                                @if ($item->producto?->laboratorio || $item->producto?->concentracion)
                                                    <br>
                                                    <small style="color: #6B7280;">
                                                        @if ($item->producto?->laboratorio)
                                                            {{ $item->producto->laboratorio->nombre }}
                                                        @endif

                                                        @if ($item->producto?->laboratorio && $item->producto?->concentracion)
                                                            |
                                                        @endif

                                                        @if ($item->producto?->concentracion)
                                                            {{ $item->producto->concentracion }}
                                                        @endif
                                                    </small>
                                                @endif

                                                <br>

                                                <small style="color: #4C1D95;">
                                                    Unidades descontadas: {{ $item->unidades_descontadas }}

                                                    @if ($item->lote)
                                                        | Lote: {{ $item->lote->numero_lote }}

                                                        @if ($item->lote->fecha_vencimiento)
                                                            | Vence: {{ $item->lote->fecha_vencimiento->format('d/m/Y') }}
                                                        @endif
                                                    @endif
                                                </small>
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
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
                                    <strong>{{ $detalleCambio->productoPresentacionDevuelta->nombre_mostrado ?? $detalleCambio->productoDevuelto->nombre_comercial ?? '-' }}</strong>
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
                                    <strong>{{ $detalleCambio->productoPresentacionNueva->nombre_mostrado ?? $detalleCambio->productoNuevo->nombre_comercial ?? '-' }}</strong>
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