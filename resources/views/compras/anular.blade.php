@extends('layouts.app')

@section('title', 'Anular compra | Santo Remedio')
@section('page-title', 'Anular compra')
@section('page-subtitle', 'Anulación controlada con ajuste de inventario')

@section('content')

@if (!auth()->user()->tienePermiso('anular_compra'))
    <div class="alert-danger">
        No tiene permiso para anular compras.
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

<form method="POST" action="{{ route('compras.anular.store', $compra) }}" id="form_anular_compra">
    @csrf

    <div class="purchase-cancel-layout">

        <section class="purchase-cancel-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-x-octagon"></i>
                            Anular compra {{ $compra->numero_compra }}
                        </h2>

                        <p>
                            Esta acción descontará del inventario el stock que ingresó por esta compra.
                        </p>
                    </div>

                    <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="detail-stat-grid compact-detail-stats">
                    <div class="detail-stat-card detail-stat-main">
                        <span>Proveedor</span>
                        <strong>{{ $compra->proveedor->nombre ?? '-' }}</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Total compra</span>
                        <strong>{{ number_format($compra->total, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Pagado</span>
                        <strong>{{ number_format($compra->monto_pagado, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Saldo pendiente</span>
                        <strong>{{ number_format($compra->saldo_pendiente, 2) }} Bs</strong>
                    </div>
                </div>
            </div>

            <div class="detail-section-card compact-section">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-box-arrow-down"></i>
                        Productos que se descontarán del inventario
                    </h3>
                </div>

                <div class="table-container compact-table-container">
                    <table class="table compact-table purchase-cancel-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Presentación</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th>Cant.</th>
                                <th>Descontar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($compra->detalles as $detalle)
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
                                    </td>

                                    <td>
                                        <strong>{{ $detalle->lote->numero_lote ?? 'Sin lote' }}</strong>
                                    </td>

                                    <td>
                                        {{ $detalle->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-' }}
                                    </td>

                                    <td>
                                        <strong>{{ $detalle->cantidad }}</strong>
                                    </td>

                                    <td>
                                        <span class="badge badge-warning">
                                            {{ $detalle->unidades_ingresadas }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-table-message">
                                        No hay productos registrados en esta compra.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <aside class="purchase-cancel-side">

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-exclamation-triangle"></i>
                        Confirmar anulación
                    </h3>
                </div>

                <div class="purchase-cancel-warning">
                    <i class="bi bi-exclamation-octagon"></i>

                    <div>
                        <strong>Acción delicada</strong>
                        <p>
                            La compra no será eliminada, pero quedará marcada como anulada.
                            También se intentará descontar del inventario el stock ingresado.
                        </p>
                    </div>
                </div>

                <div class="purchase-cancel-rules">
                    <ul>
                        <li>Si el stock ya fue vendido o movido, la anulación no será permitida.</li>
                        <li>El saldo pendiente dejará de contar como deuda activa.</li>
                        <li>La trazabilidad quedará registrada en inventario.</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label>Motivo de anulación *</label>
                    <textarea
                        name="motivo_anulacion"
                        id="motivo_anulacion"
                        rows="6"
                        required
                        placeholder="Ej: Compra registrada por error, factura duplicada, proveedor equivocado..."
                    >{{ old('motivo_anulacion') }}</textarea>

                    @error('motivo_anulacion')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="purchase-cancel-actions">
                    <a href="{{ route('compras.show', $compra) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-danger">
                        <i class="bi bi-x-octagon"></i>
                        Anular
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
const formAnularCompra = document.getElementById('form_anular_compra');
const motivoAnulacion = document.getElementById('motivo_anulacion');

formAnularCompra.addEventListener('submit', function (event) {
    event.preventDefault();

    const motivo = motivoAnulacion.value.trim();

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
        title: '¿Confirmar anulación de compra?',
        text: 'Se ajustará el inventario y la compra quedará anulada.',
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