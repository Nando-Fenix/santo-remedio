@extends('layouts.app')

@section('title', 'Anular venta | Santo Remedio')
@section('page-title', 'Anular venta')
@section('page-subtitle', 'Anulación controlada con devolución de stock y ajuste de caja')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Anular venta {{ $venta->numero_venta }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Esta acción devolverá el stock y descontará el ingreso de la caja actual.
            </p>
        </div>

        <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
            Volver
        </a>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Total venta</span>
            <h3>{{ number_format($venta->total, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Cliente</span>
            <h3>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</h3>
        </div>

        <div class="stat-card">
            <span>Sucursal</span>
            <h3>{{ $venta->sucursal->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Vendedor</span>
            <h3>{{ $venta->usuario->nombre ?? '-' }}</h3>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Productos que volverán al inventario</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Presentación</th>
                    <th>Cantidad vendida</th>
                    <th>Lotes a devolver</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->detalles as $detalle)
                    <tr>
                        <td>
                            <strong>{{ $detalle->producto->nombre_comercial ?? '-' }}</strong>
                            @if ($detalle->producto?->concentracion)
                                <br>
                                <small style="color: #6B7280;">{{ $detalle->producto->concentracion }}</small>
                            @endif
                        </td>
                        <td>{{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>
                            @forelse ($detalle->lotesDescontados as $loteDescontado)
                                <span class="badge badge-soft">
                                    {{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}
                                    | {{ $loteDescontado->unidades_descontadas }} unidades
                                </span>
                            @empty
                                <span class="badge badge-warning">Sin lote registrado</span>
                            @endforelse
                        </td>
                        <td>{{ number_format($detalle->subtotal, 2) }} Bs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    @if ($errors->any())
        <div class="alert-danger">
            <strong>Revise los siguientes errores:</strong>
            <ul style="margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('ventas.anular.store', $venta) }}" onsubmit="return confirmarFormulario(event, '¿Confirmar anulación de venta?')">
        @csrf

        <div class="form-group">
            <label>Motivo de anulación *</label>
            <textarea
                name="motivo_anulacion"
                rows="4"
                required
                placeholder="Ej: Venta registrada por error, producto equivocado, cliente canceló la compra..."
            >{{ old('motivo_anulacion') }}</textarea>

            @error('motivo_anulacion')
                <small class="error">{{ $message }}</small>
            @enderror
        </div>

        <div class="alert-danger" style="margin-top: 18px;">
            Esta acción no eliminará la venta, solo la marcará como anulada y dejará trazabilidad en inventario y caja.
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-danger">
                Anular venta
            </button>

            <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection