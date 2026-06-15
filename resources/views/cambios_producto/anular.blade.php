@extends('layouts.app')

@section('title', 'Anular cambio de producto | Santo Remedio')
@section('page-title', 'Anular cambio de producto')
@section('page-subtitle', 'Reversión controlada de inventario y caja')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Anular cambio {{ $cambioProducto->numero_cambio }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Esta acción revertirá el cambio de producto registrado.
            </p>
        </div>

        <a href="{{ route('cambios-producto.show', $cambioProducto) }}" class="btn-secondary">
            Volver
        </a>
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
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top: 0; color: #4C1D95;">Qué hará la anulación</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Acción</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Unidades</th>
                    <th>Lote</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cambioProducto->detalles as $detalle)
                    <tr>
                        <td>
                            <span class="badge badge-danger">Saldrá del inventario</span>
                        </td>
                        <td>
                            <strong>{{ $detalle->productoDevuelto->nombre_comercial ?? '-' }}</strong>
                            <br>
                            <small style="color: #6B7280;">
                                Producto que el cliente había devuelto
                            </small>
                        </td>
                        <td>{{ $detalle->cantidad_devuelta }}</td>
                        <td>{{ $detalle->unidades_devueltas }}</td>
                        <td>{{ $detalle->loteDevuelto->numero_lote ?? 'Sin lote' }}</td>
                    </tr>

                    <tr>
                        <td>
                            <span class="badge badge-success">Volverá al inventario</span>
                        </td>
                        <td>
                            <strong>{{ $detalle->productoNuevo->nombre_comercial ?? '-' }}</strong>
                            <br>
                            <small style="color: #6B7280;">
                                Producto nuevo entregado en el cambio
                            </small>
                        </td>
                        <td>{{ $detalle->cantidad_nueva }}</td>
                        <td>{{ $detalle->unidades_nuevas }}</td>
                        <td>{{ $detalle->loteNuevo->numero_lote ?? 'FEFO' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($cambioProducto->diferencia > 0)
        <div class="alert-danger" style="margin-top: 18px;">
            @if ($cambioProducto->tipo_diferencia === 'cliente_paga')
                En el cambio original el cliente pagó una diferencia de
                <strong>{{ number_format($cambioProducto->diferencia, 2) }} Bs</strong>.
                Al anular, esa diferencia saldrá de caja.
            @elseif ($cambioProducto->tipo_diferencia === 'farmacia_devuelve')
                En el cambio original la farmacia devolvió
                <strong>{{ number_format($cambioProducto->diferencia, 2) }} Bs</strong>.
                Al anular, esa diferencia se revertirá en caja.
            @endif
        </div>
    @endif
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

    <form method="POST" action="{{ route('cambios-producto.anular.store', $cambioProducto) }}" onsubmit="return confirmarFormulario(event, '¿Confirmar anulación del cambio de producto?')">
        @csrf

        <div class="form-group">
            <label>Motivo de anulación *</label>
            <textarea
                name="motivo_anulacion"
                rows="4"
                required
                placeholder="Ej: Cambio registrado por error, producto incorrecto, reversión autorizada..."
            >{{ old('motivo_anulacion') }}</textarea>

            @error('motivo_anulacion')
                <small class="error">{{ $message }}</small>
            @enderror
        </div>

        <div class="alert-danger" style="margin-top: 18px;">
            Esta acción no eliminará el cambio. Solo lo marcará como anulado y dejará movimientos de inventario y caja.
            Si el producto devuelto ya fue vendido o movido, la anulación será bloqueada.
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-danger">
                Anular cambio
            </button>

            <a href="{{ route('cambios-producto.show', $cambioProducto) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection