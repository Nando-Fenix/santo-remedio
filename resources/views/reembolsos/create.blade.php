@extends('layouts.app')

@section('title', 'Registrar reembolso | Santo Remedio')
@section('page-title', 'Registrar reembolso')
@section('page-subtitle', 'Devolución parcial de productos vendidos')

@section('content')

@if (!auth()->user()->tienePermiso('reembolsar_venta'))
    <div class="alert-danger">
        No tiene permiso para registrar reembolsos.
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

<form method="POST" action="{{ route('reembolsos.store', $venta) }}" id="form_reembolso">
    @csrf

    <div class="refund-layout">

        <section class="refund-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-cash-coin"></i>
                            Reembolso de venta {{ $venta->numero_venta }}
                        </h2>

                        <p>
                            Seleccione los productos y cantidades que serán devueltos.
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
                        Productos vendidos
                    </h3>
                </div>

                <div class="table-container compact-table-container">
                    <table class="table compact-table refund-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Presentación</th>
                                <th>Vendida</th>
                                <th>Devuelta</th>
                                <th>Disponible</th>
                                <th>P. Unit.</th>
                                <th>Devolver</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($venta->detalles as $index => $detalle)
                                @php
                                    $cantidadYaDevuelta = $detalle->reembolsos()
                                        ->whereHas('reembolso', function ($query) {
                                            $query->where('estado', 'registrado');
                                        })
                                        ->sum('cantidad_devuelta');

                                    $cantidadDisponible = $detalle->cantidad - $cantidadYaDevuelta;
                                @endphp

                                <tr>
                                    <td>
                                        <strong>{{ $detalle->producto->nombre_comercial ?? '-' }}</strong>

                                        @if ($detalle->producto?->concentracion)
                                            <br>
                                            <small style="color:#6B7280;">
                                                {{ $detalle->producto->concentracion }}
                                            </small>
                                        @endif

                                        <div class="mini-lot-list" style="margin-top:5px;">
                                            @forelse ($detalle->lotesDescontados as $loteDescontado)
                                                <div class="mini-lot-line">
                                                    <strong>{{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}</strong>
                                                    <small>{{ $loteDescontado->unidades_descontadas }} unidad(es)</small>
                                                </div>
                                            @empty
                                                <small style="color:#6B7280;">Sin lote</small>
                                            @endforelse
                                        </div>
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
                                        {{ $cantidadYaDevuelta }}
                                    </td>

                                    <td>
                                        @if ($cantidadDisponible > 0)
                                            <span class="badge badge-success">{{ $cantidadDisponible }}</span>
                                        @else
                                            <span class="badge badge-danger">0</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ number_format($detalle->precio_unitario, 2) }} Bs
                                    </td>

                                    <td>
                                        <input
                                            type="hidden"
                                            name="items[{{ $index }}][detalle_venta_id]"
                                            value="{{ $detalle->id }}"
                                        >

                                        <input
                                            type="number"
                                            name="items[{{ $index }}][cantidad_devuelta]"
                                            min="0"
                                            max="{{ $cantidadDisponible }}"
                                            value="0"
                                            class="refund-quantity-input"
                                            data-precio="{{ $detalle->precio_unitario }}"
                                            data-disponible="{{ $cantidadDisponible }}"
                                            {{ $cantidadDisponible <= 0 ? 'disabled' : '' }}
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <aside class="refund-side">

            <div class="cancel-warning-card">
                <div class="cancel-warning-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div>
                    <h3>Acción delicada</h3>
                    <p>
                        El reembolso devolverá stock al inventario y descontará el monto correspondiente de la caja actual.
                    </p>
                </div>
            </div>

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-pencil-square"></i>
                        Confirmar reembolso
                    </h3>
                </div>

                <div class="refund-total-preview">
                    <span>Total estimado a reembolsar</span>
                    <strong id="total_reembolso_preview">0.00 Bs</strong>
                </div>

                <div class="form-group">
                    <label>Motivo del reembolso *</label>
                    <textarea
                        name="motivo"
                        rows="7"
                        required
                        placeholder="Ej: Cliente devolvió producto por error de compra, medicamento equivocado, devolución autorizada..."
                    >{{ old('motivo') }}</textarea>

                    @error('motivo')
                        <small class="error">{{ $message }}</small>
                    @enderror

                    <small class="cancel-help-text">
                        El motivo debe ser claro para mantener trazabilidad.
                    </small>
                </div>

                <div class="refund-actions">
                    <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-danger">
                        <i class="bi bi-cash-coin"></i>
                        Registrar
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
const formReembolso = document.getElementById('form_reembolso');
const inputsReembolso = document.querySelectorAll('.refund-quantity-input');
const totalPreview = document.getElementById('total_reembolso_preview');

function actualizarTotalReembolso() {
    let total = 0;

    inputsReembolso.forEach((input) => {
        const cantidad = Number(input.value || 0);
        const precio = Number(input.dataset.precio || 0);

        total += cantidad * precio;
    });

    totalPreview.textContent = total.toFixed(2) + ' Bs';
}

inputsReembolso.forEach((input) => {
    input.addEventListener('input', function () {
        const disponible = Number(this.dataset.disponible || 0);
        let cantidad = Number(this.value || 0);

        if (cantidad < 0) {
            cantidad = 0;
        }

        if (cantidad > disponible) {
            cantidad = disponible;
        }

        this.value = cantidad;
        actualizarTotalReembolso();
    });
});

formReembolso.addEventListener('submit', function (event) {
    event.preventDefault();

    let totalCantidad = 0;

    inputsReembolso.forEach((input) => {
        totalCantidad += Number(input.value || 0);
    });

    const motivo = document.querySelector('textarea[name="motivo"]').value.trim();

    if (totalCantidad <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin productos seleccionados',
            text: 'Debe ingresar al menos una cantidad para devolver.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (motivo.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Motivo requerido',
            text: 'El motivo debe tener al menos 5 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'warning',
        title: '¿Confirmar reembolso?',
        text: 'Se devolverá stock al inventario y se descontará el monto correspondiente de caja.',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});

actualizarTotalReembolso();
</script>

@endif

@endsection