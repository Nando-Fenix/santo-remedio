@extends('layouts.app')

@section('title', 'Detalle de reembolso | Santo Remedio')
@section('page-title', 'Detalle de reembolso')
@section('page-subtitle', 'Historial de devolución y ajuste de inventario')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Reembolso {{ $reembolso->numero_reembolso }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Registrado el {{ $reembolso->fecha_reembolso->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('ventas.show', $reembolso->venta) }}" class="btn-primary">
                Ver venta
            </a>

            <a href="{{ route('ventas.index') }}" class="btn-secondary">
                Volver a ventas
            </a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Venta</span>
            <h3>{{ $reembolso->venta->numero_venta ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Monto reembolsado</span>
            <h3>{{ number_format($reembolso->monto_total, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Sucursal</span>
            <h3>{{ $reembolso->sucursal->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Registrado por</span>
            <h3>{{ $reembolso->usuario->nombre ?? '-' }}</h3>
        </div>
    </div>

    <p style="margin-top: 18px;">
        <strong>Motivo:</strong> {{ $reembolso->motivo }}
    </p>

    @if ($reembolso->estado === 'anulado')
        <div class="alert-danger" style="margin-top: 14px;">
            Este reembolso fue anulado.
        </div>
    @endif
</div>

<div class="card">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos devueltos</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Presentación</th>
                    <th>Lote</th>
                    <th>Cantidad devuelta</th>
                    <th>Unidades devueltas</th>
                    <th>Monto devuelto</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reembolso->detalles as $detalle)
                    <tr>
                        <td>
                            <strong>{{ $detalle->producto->nombre_comercial ?? '-' }}</strong>

                            @if ($detalle->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">
                                    {{ $detalle->producto->concentracion }}
                                </small>
                            @endif
                        </td>

                        <td>{{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}</td>
                        <td>{{ $detalle->lote->numero_lote ?? 'Sin lote' }}</td>
                        <td>{{ $detalle->cantidad_devuelta }}</td>
                        <td>{{ $detalle->unidades_devueltas }}</td>
                        <td>{{ number_format($detalle->monto_devuelto, 2) }} Bs</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6B7280;">
                            No hay productos registrados en este reembolso.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
