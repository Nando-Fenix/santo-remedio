@extends('layouts.app')

@section('title', 'Anular venta | Santo Remedio')
@section('page-title', 'Anular venta')
@section('page-subtitle', 'Anulación controlada con devolución de stock y ajuste de caja')

@section('content')

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

<div class="cancel-sale-layout">

    <section class="cancel-sale-main">

        <div class="compact-card">
            <div class="compact-header">
                <div>
                    <h2>
                        <i class="bi bi-x-octagon"></i>
                        Anular venta {{ $venta->numero_venta }}
                    </h2>

                    <p>
                        Esta acción devolverá el stock y descontará el ingreso de la caja actual.
                    </p>
                </div>

                <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>

            <div class="detail-stat-grid compact-detail-stats">
                <div class="detail-stat-card detail-stat-main">
                    <span>Total venta</span>
                    <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Cliente</span>
                    <strong>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Sucursal</span>
                    <strong>{{ $venta->sucursal->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Vendedor</span>
                    <strong>{{ $venta->usuario->nombre ?? '-' }}</strong>
                </div>
            </div>
        </div>

        <div class="detail-section-card compact-section">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-capsule"></i>
                    Productos que volverán al inventario
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table cancel-sale-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Presentación</th>
                            <th>Cant.</th>
                            <th>Lotes a devolver</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($venta->detalles as $detalle)
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
                                    <strong>{{ $detalle->cantidad }}</strong>
                                </td>

                                <td>
                                    <div class="mini-lot-list">
                                        @forelse ($detalle->lotesDescontados as $loteDescontado)
                                            <div class="mini-lot-line">
                                                <strong>{{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}</strong>
                                                <small>
                                                    {{ $loteDescontado->unidades_descontadas }} unidad(es)

                                                    @if ($loteDescontado->lote?->fecha_vencimiento)
                                                        · Vence: {{ $loteDescontado->lote->fecha_vencimiento->format('d/m/Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                        @empty
                                            <span class="badge badge-warning">Sin lote registrado</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td>
                                    <strong>{{ number_format($detalle->subtotal, 2) }} Bs</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-table-message">
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
                        Promociones que volverán al inventario
                    </h3>
                </div>

                <div class="table-container compact-table-container">
                    <table class="table compact-table cancel-sale-table">
                        <thead>
                            <tr>
                                <th>Promoción</th>
                                <th>Cant.</th>
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

                                    <td>
                                        <strong>{{ $detallePromo->cantidad }}</strong>
                                    </td>

                                    <td>
                                        <strong>{{ number_format($detallePromo->subtotal, 2) }} Bs</strong>
                                    </td>

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

    </section>

    <aside class="cancel-sale-side">

        <div class="cancel-warning-card">
            <div class="cancel-warning-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>

            <div>
                <h3>Acción delicada</h3>
                <p>
                    La venta no se eliminará. Quedará marcada como anulada y se registrará la devolución de stock y el ajuste de caja.
                </p>
            </div>
        </div>

        <div class="cancel-form-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-pencil-square"></i>
                    Confirmar anulación
                </h3>
            </div>

            <form method="POST" action="{{ route('ventas.anular.store', $venta) }}" id="form_anular_venta">
                @csrf

                <div class="form-group">
                    <label>Motivo de anulación *</label>
                    <textarea
                        name="motivo_anulacion"
                        rows="7"
                        required
                        placeholder="Ej: Venta registrada por error, producto equivocado, cliente canceló la compra..."
                    >{{ old('motivo_anulacion') }}</textarea>

                    @error('motivo_anulacion')
                        <small class="error">{{ $message }}</small>
                    @enderror

                    <small class="cancel-help-text">
                        El motivo debe ser claro para mantener trazabilidad.
                    </small>
                </div>

                <div class="cancel-final-summary">
                    <div>
                        <span>Total a descontar de caja</span>
                        <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                    </div>

                    <div>
                        <span>Productos individuales</span>
                        <strong>{{ $venta->detalles->count() }}</strong>
                    </div>

                    @if ($venta->promociones->count() > 0)
                        <div>
                            <span>Promociones</span>
                            <strong>{{ $venta->promociones->count() }}</strong>
                        </div>
                    @endif
                </div>

                <div class="cancel-sale-actions">
                    <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-danger">
                        <i class="bi bi-x-octagon"></i>
                        Anular venta
                    </button>
                </div>
            </form>
        </div>

    </aside>

</div>

<script>
document.getElementById('form_anular_venta').addEventListener('submit', function (event) {
    event.preventDefault();

    const motivo = document.querySelector('textarea[name="motivo_anulacion"]').value.trim();

    if (motivo.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Motivo requerido',
            text: 'El motivo de anulación debe tener al menos 5 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'warning',
        title: '¿Confirmar anulación de venta?',
        text: 'Se devolverá el stock y se descontará el ingreso de la caja actual.',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular venta',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});
</script>

@endsection