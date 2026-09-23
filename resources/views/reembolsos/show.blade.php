@extends('layouts.app')

@section('title', 'Detalle de reembolso | Santo Remedio')
@section('page-title', 'Detalle de reembolso')
@section('page-subtitle', 'Historial de devolución y ajuste de inventario')

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
                <i class="bi bi-cash-coin"></i>
                Reembolso {{ $reembolso->numero_reembolso }}
            </h2>

            <p>
                Registrado el {{ $reembolso->fecha_reembolso->format('d/m/Y H:i') }}
                ·
                Venta: <strong>{{ $reembolso->venta->numero_venta ?? '-' }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            @if ($reembolso->estado === 'registrado')
                <span class="badge badge-success">Registrado</span>
            @else
                <span class="badge badge-danger">Anulado</span>
            @endif

            <a href="{{ route('ventas.show', $reembolso->venta) }}" class="btn-primary">
                <i class="bi bi-receipt"></i>
                Ver venta
            </a>

            <a href="{{ route('ventas.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="detail-stat-grid compact-detail-stats">
        <div class="detail-stat-card detail-stat-main">
            <span>Monto reembolsado</span>
            <strong>{{ number_format($reembolso->monto_total, 2) }} Bs</strong>
        </div>

        <div class="detail-stat-card">
            <span>Venta</span>
            <strong>{{ $reembolso->venta->numero_venta ?? '-' }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Sucursal</span>
            <strong>{{ $reembolso->sucursal->nombre ?? '-' }}</strong>
        </div>

        <div class="detail-stat-card">
            <span>Registrado por</span>
            <strong>{{ $reembolso->usuario->nombre ?? '-' }}</strong>
        </div>
    </div>

    <div class="refund-show-mini-info">
        <div>
            <span>Cliente</span>
            <strong>{{ $reembolso->venta->cliente->nombre ?? 'Consumidor final' }}</strong>
        </div>

        <div>
            <span>Fecha</span>
            <strong>{{ $reembolso->fecha_reembolso->format('d/m/Y H:i') }}</strong>
        </div>

        <div>
            <span>Estado</span>
            <strong>{{ ucfirst($reembolso->estado) }}</strong>
        </div>

        <div>
            <span>Motivo</span>
            <strong>{{ $reembolso->motivo }}</strong>
        </div>
    </div>

    @if ($reembolso->estado === 'anulado')
        <div class="alert-danger" style="margin-bottom: 12px;">
            Este reembolso fue anulado.
        </div>
    @endif

    <div class="detail-section-card compact-section">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-capsule"></i>
                Productos devueltos
            </h3>
        </div>

        <div class="table-container compact-table-container">
            <table class="table compact-table refund-show-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Presentación</th>
                        <th>Lote</th>
                        <th>Cant.</th>
                        <th>Unidades</th>
                        <th>Monto</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($reembolso->detalles as $detalle)
                        <tr>
                            <td>
                                <strong>{{ $detalle->producto->nombre_comercial ?? '-' }}</strong>

                                @if ($detalle->producto?->concentracion)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $detalle->producto->concentracion }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}

                                @if ($detalle->productoPresentacion?->unidades_equivalentes)
                                    <br>
                                    <small style="color:#6B7280;">
                                        {{ $detalle->productoPresentacion->unidades_equivalentes }} unidad(es)
                                    </small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $detalle->lote->numero_lote ?? 'Sin lote' }}</strong>

                                @if ($detalle->lote?->fecha_vencimiento)
                                    <br>
                                    <small style="color:#6B7280;">
                                        Vence: {{ $detalle->lote->fecha_vencimiento->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $detalle->cantidad_devuelta }}</strong>
                            </td>

                            <td>
                                {{ $detalle->unidades_devueltas }}
                            </td>

                            <td>
                                <strong>{{ number_format($detalle->monto_devuelto, 2) }} Bs</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-table-message">
                                No hay productos registrados en este reembolso.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection