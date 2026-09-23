@extends('layouts.app')

@section('title', 'Anular cambio de producto | Santo Remedio')
@section('page-title', 'Anular cambio de producto')
@section('page-subtitle', 'Reversión controlada de inventario y caja')

@section('content')

@if (!auth()->user()->tienePermiso('anular_cambio_producto'))
    <div class="alert-danger">
        No tiene permiso para anular cambios de producto.
    </div>
@else

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

<div class="cancel-exchange-layout">

    <section class="cancel-exchange-main">

        <div class="compact-card">
            <div class="compact-header">
                <div>
                    <h2>
                        <i class="bi bi-x-octagon"></i>
                        Anular cambio {{ $cambioProducto->numero_cambio }}
                    </h2>

                    <p>
                        Esta acción revertirá el cambio de producto registrado.
                    </p>
                </div>

                <a href="{{ route('cambios-producto.show', $cambioProducto) }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>

            <div class="detail-stat-grid compact-detail-stats">
                <div class="detail-stat-card detail-stat-main">
                    <span>Diferencia</span>
                    <strong>{{ number_format($cambioProducto->diferencia, 2) }} Bs</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Venta</span>
                    <strong>{{ $cambioProducto->venta->numero_venta ?? '-' }}</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Monto devuelto</span>
                    <strong>{{ number_format($cambioProducto->monto_devuelto, 2) }} Bs</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Monto nuevo</span>
                    <strong>{{ number_format($cambioProducto->monto_nuevo, 2) }} Bs</strong>
                </div>
            </div>
        </div>

        <div class="detail-section-card compact-section">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-arrow-left-right"></i>
                    Qué hará la anulación
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table cancel-exchange-table">
                    <thead>
                        <tr>
                            <th>Acción</th>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Unidades</th>
                            <th>Lote</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cambioProducto->detalles as $detalle)
                            <tr>
                                <td>
                                    <span class="badge badge-danger">Saldrá</span>
                                </td>

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
                                        Producto que el cliente había devuelto
                                    </small>
                                </td>

                                <td>
                                    <strong>{{ $detalle->cantidad_devuelta }}</strong>
                                </td>

                                <td>
                                    {{ $detalle->unidades_devueltas }}
                                </td>

                                <td>
                                    {{ $detalle->loteDevuelto->numero_lote ?? 'Sin lote' }}
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="badge badge-success">Volverá</span>
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
                                        Producto nuevo entregado en el cambio
                                    </small>
                                </td>

                                <td>
                                    <strong>{{ $detalle->cantidad_nueva }}</strong>
                                </td>

                                <td>
                                    {{ $detalle->unidades_nuevas }}
                                </td>

                                <td>
                                    {{ $detalle->loteNuevo->numero_lote ?? 'FEFO' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($cambioProducto->diferencia > 0)
            <div class="cancel-exchange-cash-card">
                <div>
                    <i class="bi bi-cash-coin"></i>
                </div>

                <p>
                    @if ($cambioProducto->tipo_diferencia === 'cliente_paga')
                        En el cambio original el cliente pagó una diferencia de
                        <strong>{{ number_format($cambioProducto->diferencia, 2) }} Bs</strong>.
                        Al anular, esa diferencia saldrá de caja.
                    @elseif ($cambioProducto->tipo_diferencia === 'farmacia_devuelve')
                        En el cambio original la farmacia devolvió
                        <strong>{{ number_format($cambioProducto->diferencia, 2) }} Bs</strong>.
                        Al anular, esa diferencia se revertirá en caja.
                    @endif
                </p>
            </div>
        @endif

    </section>

    <aside class="cancel-exchange-side">

        <div class="cancel-warning-card">
            <div class="cancel-warning-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>

            <div>
                <h3>Acción delicada</h3>
                <p>
                    Esta acción no eliminará el cambio. Solo lo marcará como anulado y dejará movimientos de inventario y caja.
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

            <form method="POST" action="{{ route('cambios-producto.anular.store', $cambioProducto) }}" id="form_anular_cambio_producto">
                @csrf

                <div class="form-group">
                    <label>Motivo de anulación *</label>
                    <textarea
                        name="motivo_anulacion"
                        rows="7"
                        required
                        placeholder="Ej: Cambio registrado por error, producto incorrecto, reversión autorizada..."
                    >{{ old('motivo_anulacion') }}</textarea>

                    @error('motivo_anulacion')
                        <small class="error">{{ $message }}</small>
                    @enderror

                    <small class="cancel-help-text">
                        Si el producto devuelto ya fue vendido o movido, la anulación será bloqueada.
                    </small>
                </div>

                <div class="cancel-final-summary">
                    <div>
                        <span>Diferencia a revertir</span>
                        <strong>{{ number_format($cambioProducto->diferencia, 2) }} Bs</strong>
                    </div>

                    <div>
                        <span>Tipo</span>
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

                <div class="cancel-exchange-actions">
                    <a href="{{ route('cambios-producto.show', $cambioProducto) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-danger">
                        <i class="bi bi-x-octagon"></i>
                        Anular
                    </button>
                </div>
            </form>
        </div>

    </aside>

</div>

<script>
document.getElementById('form_anular_cambio_producto').addEventListener('submit', function (event) {
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
        title: '¿Confirmar anulación del cambio?',
        text: 'Se revertirá el inventario y el movimiento de caja correspondiente.',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
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

@endif

@endsection