@extends('layouts.app')

@section('title', 'Anular atención de servicio | Santo Remedio')
@section('page-title', 'Anular atención de servicio')
@section('page-subtitle', 'Reversión de cobro e insumos descontados')

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

<div class="cancel-service-layout">

    <section class="cancel-service-main">

        <div class="compact-card">
            <div class="compact-header">
                <div>
                    <h2>
                        <i class="bi bi-x-octagon"></i>
                        Anular atención #{{ $atencionServicio->numero_atencion ?? 'SER-' . str_pad($atencionServicio->id, 6, '0', STR_PAD_LEFT) }}
                    </h2>

                    <p>
                        Esta acción devolverá insumos al inventario y restará el ingreso de caja.
                    </p>
                </div>

                <a href="{{ route('atenciones-servicio.show', $atencionServicio) }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>

            <div class="detail-stat-grid">
                <div class="detail-stat-card">
                    <span>Servicio</span>
                    <strong>{{ $atencionServicio->servicio->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-stat-card detail-stat-main">
                    <span>Total cobrado</span>
                    <strong>{{ number_format($atencionServicio->total, 2) }} Bs</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Método</span>
                    <strong>{{ $atencionServicio->metodoPago->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Fecha</span>
                    <strong>{{ $atencionServicio->fecha_hora->format('d/m/Y H:i') }}</strong>
                </div>
            </div>
        </div>

        <div class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-clipboard2-pulse"></i>
                    Datos de la atención
                </h3>
            </div>

            <div class="detail-info-grid">
                <div class="detail-info-item">
                    <span>Cliente</span>
                    <strong>{{ $atencionServicio->cliente->nombre ?? 'Consumidor final' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Cantidad</span>
                    <strong>{{ $atencionServicio->cantidad }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Precio unitario</span>
                    <strong>{{ number_format($atencionServicio->precio_unitario, 2) }} Bs</strong>
                </div>

                <div class="detail-info-item">
                    <span>Subtotal</span>
                    <strong>{{ number_format($atencionServicio->subtotal, 2) }} Bs</strong>
                </div>

                <div class="detail-info-item">
                    <span>Descuento</span>
                    <strong>{{ number_format($atencionServicio->descuento, 2) }} Bs</strong>
                </div>

                <div class="detail-info-item">
                    <span>Registrado por</span>
                    <strong>{{ $atencionServicio->usuario->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item detail-info-full">
                    <span>Observación</span>
                    <strong>{{ $atencionServicio->observacion ?? '-' }}</strong>
                </div>
            </div>
        </div>

        <div class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Insumos que serán devueltos
                </h3>
            </div>

            <div class="table-container compact-table-container">
                <table class="table compact-table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Lote</th>
                            <th>Devolver</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($atencionServicio->insumos as $insumo)
                            <tr>
                                <td>
                                    <strong>
                                        {{ $insumo->productoPresentacion->nombre_mostrado ?? $insumo->producto->nombre_comercial ?? '-' }}
                                    </strong>

                                    <br>

                                    <small style="color:#6B7280;">
                                        {{ $insumo->productoPresentacion->presentacion->nombre ?? '-' }}

                                        @if ($insumo->producto?->laboratorio)
                                            · {{ $insumo->producto->laboratorio->nombre }}
                                        @endif

                                        @if ($insumo->producto?->concentracion)
                                            · {{ $insumo->producto->concentracion }}
                                        @endif
                                    </small>
                                </td>

                                <td>
                                    {{ $insumo->lote->numero_lote ?? 'Sin lote' }}

                                    @if ($insumo->lote?->fecha_vencimiento)
                                        <br>
                                        <small style="color:#6B7280;">
                                            Vence: {{ $insumo->lote->fecha_vencimiento->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $insumo->unidades_descontadas }}</strong>
                                    <br>
                                    <small style="color:#6B7280;">unidad(es)</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-table-message">
                                    Esta atención no descontó insumos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>

    <aside class="cancel-service-side">

        <div class="cancel-warning-card">
            <div class="cancel-warning-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>

            <div>
                <h3>Acción delicada</h3>
                <p>
                    Al confirmar, el sistema revertirá el cobro registrado en caja y devolverá los insumos descontados.
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

            <form method="POST" action="{{ route('atenciones-servicio.anular.store', $atencionServicio) }}" id="form_anular_atencion">
                @csrf

                <div class="form-group">
                    <label>Motivo de anulación *</label>
                    <textarea
                        name="motivo_anulacion"
                        rows="7"
                        required
                        placeholder="Ej: Error en el registro, servicio no realizado, cobro incorrecto..."
                    >{{ old('motivo_anulacion') }}</textarea>

                    <small class="cancel-help-text">
                        Debe registrar un motivo claro para auditoría.
                    </small>
                </div>

                <div class="cancel-final-summary">
                    <div>
                        <span>Total a revertir</span>
                        <strong>{{ number_format($atencionServicio->total, 2) }} Bs</strong>
                    </div>

                    <div>
                        <span>Insumos a devolver</span>
                        <strong>{{ $atencionServicio->insumos->count() }}</strong>
                    </div>
                </div>

                <div class="cancel-service-actions">
                    <a href="{{ route('atenciones-servicio.show', $atencionServicio) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-danger">
                        <i class="bi bi-x-octagon"></i>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>

    </aside>

</div>

<script>
document.getElementById('form_anular_atencion').addEventListener('submit', function (event) {
    event.preventDefault();

    const motivo = document.querySelector('textarea[name="motivo_anulacion"]').value.trim();

    if (motivo.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Motivo requerido',
            text: 'Debe ingresar un motivo de al menos 5 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'warning',
        title: '¿Anular atención?',
        text: 'Se devolverán los insumos al inventario y se restará el ingreso de caja.',
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

@endsection