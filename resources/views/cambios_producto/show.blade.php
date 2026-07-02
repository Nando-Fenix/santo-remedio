@extends('layouts.app')

@section('title', 'Detalle de cambio | Santo Remedio')
@section('page-title', 'Detalle de cambio de producto')
@section('page-subtitle', 'Historial de devolución y entrega de producto nuevo')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Cambio {{ $cambioProducto->numero_cambio }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Registrado el {{ $cambioProducto->fecha_cambio->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if (
                $cambioProducto->estado === 'registrado'
                && $cambioProducto->caja?->estado === 'abierta'
                && auth()->user()->tienePermiso('anular_cambio_producto')
            )
                <a href="{{ route('cambios-producto.anular.create', $cambioProducto) }}" class="btn-danger">
                    Anular cambio
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_ventas'))
                <a href="{{ route('ventas.show', $cambioProducto->venta) }}" class="btn-primary">
                    Ver venta
                </a>

                <a href="{{ route('ventas.index') }}" class="btn-secondary">
                    Volver a ventas
                </a>
            @endif
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Venta</span>
            <h3>{{ $cambioProducto->venta->numero_venta ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Monto devuelto</span>
            <h3>{{ number_format($cambioProducto->monto_devuelto, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Monto nuevo</span>
            <h3>{{ number_format($cambioProducto->monto_nuevo, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Diferencia</span>
            <h3>{{ number_format($cambioProducto->diferencia, 2) }} Bs</h3>
            <small style="color: #6B7280;">
                @if ($cambioProducto->tipo_diferencia === 'cliente_paga')
                    Cliente pagó diferencia
                @elseif ($cambioProducto->tipo_diferencia === 'farmacia_devuelve')
                    Farmacia devolvió diferencia
                @else
                    Sin diferencia
                @endif
            </small>
        </div>
    </div>

    <p style="margin-top: 18px;">
        <strong>Motivo:</strong> {{ $cambioProducto->motivo }}
    </p>

    @if ($cambioProducto->estado === 'anulado')
        <div class="alert-danger" style="margin-top: 14px;">
            <strong>Este cambio fue anulado.</strong>

            @if ($cambioProducto->fecha_anulacion)
                <br>
                <strong>Fecha anulación:</strong> {{ $cambioProducto->fecha_anulacion->format('d/m/Y H:i') }}
            @endif

            @if ($cambioProducto->usuarioAnulacion)
                <br>
                <strong>Anulado por:</strong> {{ $cambioProducto->usuarioAnulacion->nombre ?? '-' }}
            @endif

            @if ($cambioProducto->motivo_anulacion)
                <br>
                <strong>Motivo:</strong> {{ $cambioProducto->motivo_anulacion }}
            @endif
        </div>
    @endif
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #4C1D95;">Detalle del cambio</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto devuelto</th>
                    <th>Cantidad devuelta</th>
                    <th>Monto devuelto</th>
                    <th>Producto nuevo</th>
                    <th>Cantidad nueva</th>
                    <th>Monto nuevo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cambioProducto->detalles as $detalle)
                    <tr>
                        <td>
                            <strong>{{ $detalle->productoDevuelto->nombre_comercial ?? '-' }}</strong>

                            @if ($detalle->productoDevuelto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">
                                    {{ $detalle->productoDevuelto->concentracion }}
                                </small>
                            @endif

                            <br>
                            <small style="color: #6B7280;">
                                Presentación: {{ $detalle->productoPresentacionDevuelta->nombre_mostrado ?? '-' }}
                            </small>

                            <br>
                            <small style="color: #6B7280;">
                                Lote devuelto: {{ $detalle->loteDevuelto->numero_lote ?? 'Según venta original' }}
                            </small>
                        </td>

                        <td>
                            {{ $detalle->cantidad_devuelta }}
                            <br>
                            <small style="color: #6B7280;">
                                {{ $detalle->unidades_devueltas }} unidades
                            </small>
                        </td>

                        <td>{{ number_format($detalle->monto_devuelto, 2) }} Bs</td>

                        <td>
                            <strong>{{ $detalle->productoNuevo->nombre_comercial ?? '-' }}</strong>

                            @if ($detalle->productoNuevo?->concentracion)
                                <br>
                                <small style="color: #6B7280;">
                                    {{ $detalle->productoNuevo->concentracion }}
                                </small>
                            @endif

                            <br>
                            <small style="color: #6B7280;">
                                Presentación: {{ $detalle->productoPresentacionNueva->nombre_mostrado ?? '-' }}
                            </small>

                            <br>
                            <small style="color: #6B7280;">
                                Lote nuevo: {{ $detalle->loteNuevo->numero_lote ?? 'FEFO' }}
                            </small>
                        </td>

                        <td>
                            {{ $detalle->cantidad_nueva }}
                            <br>
                            <small style="color: #6B7280;">
                                {{ $detalle->unidades_nuevas }} unidades
                            </small>
                        </td>

                        <td>{{ number_format($detalle->monto_nuevo, 2) }} Bs</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6B7280;">
                            No hay detalle registrado para este cambio.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection