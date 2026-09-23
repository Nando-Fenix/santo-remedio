@extends('layouts.app')

@section('title', 'Detalle de cambio | Santo Remedio')
@section('page-title', 'Detalle de cambio de producto')
@section('page-subtitle', 'Historial de devolución y entrega de producto nuevo')

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
                <i class="bi bi-arrow-left-right"></i>
                Cambio {{ $cambioProducto->numero_cambio }}
            </h2>

            <p>
                Registrado el {{ $cambioProducto->fecha_cambio->format('d/m/Y H:i') }}
                ·
                Venta: <strong>{{ $cambioProducto->venta->numero_venta ?? '-' }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            @if ($cambioProducto->estado === 'registrado')
                <span class="badge badge-success">Registrado</span>
            @else
                <span class="badge badge-danger">Anulado</span>
            @endif

            @if (
                $cambioProducto->estado === 'registrado'
                && $cambioProducto->caja?->estado === 'abierta'
                && auth()->user()->tienePermiso('anular_cambio_producto')
            )
                <a href="{{ route('cambios-producto.anular.create', $cambioProducto) }}" class="btn-danger">
                    <i class="bi bi-x-octagon"></i>
                    Anular
                </a>
            @endif

            @if (auth()->user()->tienePermiso('ver_ventas'))
                <a href="{{ route('ventas.show', $cambioProducto->venta) }}" class="btn-primary">
                    <i class="bi bi-receipt"></i>
                    Ver venta
                </a>

                <a href="{{ route('ventas.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            @endif
        </div>
    </div>

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Diferencia</span>
            <strong>{{ number_format($cambioProducto->diferencia, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Monto devuelto</span>
            <strong>{{ number_format($cambioProducto->monto_devuelto, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Monto nuevo</span>
            <strong>{{ number_format($cambioProducto->monto_nuevo, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Tipo de diferencia</span>
            <strong>
                @if ($cambioProducto->tipo_diferencia === 'cliente_paga')
                    Cliente pagó
                @elseif ($cambioProducto->tipo_diferencia === 'farmacia_devuelve')
                    Farmacia devolvió
                @else
                    Sin diferencia
                @endif
            </strong>
        </div>
    </div>

    <div class="exchange-show-mini-info">
        <div>
            <span>Venta</span>
            <strong>{{ $cambioProducto->venta->numero_venta ?? '-' }}</strong>
        </div>

        <div>
            <span>Fecha</span>
            <strong>{{ $cambioProducto->fecha_cambio->format('d/m/Y H:i') }}</strong>
        </div>

        <div>
            <span>Estado</span>
            <strong>{{ ucfirst($cambioProducto->estado) }}</strong>
        </div>

        <div>
            <span>Motivo</span>
            <strong>{{ $cambioProducto->motivo }}</strong>
        </div>
    </div>

    @if ($cambioProducto->estado === 'anulado')
        <div class="exchange-cancel-info">
            <div>
                <strong>
                    <i class="bi bi-x-octagon"></i>
                    Este cambio fue anulado
                </strong>

                <span>
                    Fecha:
                    {{ $cambioProducto->fecha_anulacion?->format('d/m/Y H:i') ?? '-' }}
                </span>

                <span>
                    Anulado por:
                    {{ $cambioProducto->usuarioAnulacion->nombre ?? '-' }}
                </span>

                <span>
                    Motivo:
                    {{ $cambioProducto->motivo_anulacion ?? '-' }}
                </span>
            </div>
        </div>
    @endif

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-list-check"></i>
                Detalle del cambio
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table exchange-show-table">
                <thead>
                    <tr>
                        <th>Producto devuelto</th>
                        <th>Cant.</th>
                        <th>Monto</th>
                        <th>Producto nuevo</th>
                        <th>Cant.</th>
                        <th>Monto</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($cambioProducto->detalles as $detalle)
                        <tr>
                            <td>
                                <strong>{{ $detalle->productoDevuelto->nombre_comercial ?? '-' }}</strong>

                                @if ($detalle->productoDevuelto?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $detalle->productoDevuelto->concentracion }}
                                    </small>
                                @endif

                                <br>
                                <small style="color:#6B7280;">
                                    {{ $detalle->productoPresentacionDevuelta->nombre_mostrado ?? '-' }}
                                </small>

                                <br>
                                <small style="color:#4C1D95;">
                                    Lote: {{ $detalle->loteDevuelto->numero_lote ?? 'Según venta original' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $detalle->cantidad_devuelta }}</strong>
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $detalle->unidades_devueltas }} unidades
                                </small>
                            </td>

                            <td>
                                <strong>{{ number_format($detalle->monto_devuelto, 2) }} Bs</strong>
                            </td>

                            <td>
                                <strong>{{ $detalle->productoNuevo->nombre_comercial ?? '-' }}</strong>

                                @if ($detalle->productoNuevo?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $detalle->productoNuevo->concentracion }}
                                    </small>
                                @endif

                                <br>
                                <small style="color:#6B7280;">
                                    {{ $detalle->productoPresentacionNueva->nombre_mostrado ?? '-' }}
                                </small>

                                <br>
                                <small style="color:#4C1D95;">
                                    Lote: {{ $detalle->loteNuevo->numero_lote ?? 'FEFO' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $detalle->cantidad_nueva }}</strong>
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $detalle->unidades_nuevas }} unidades
                                </small>
                            </td>

                            <td>
                                <strong>{{ number_format($detalle->monto_nuevo, 2) }} Bs</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-table-message">
                                No hay detalle registrado para este cambio.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection