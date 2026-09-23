@extends('layouts.app')

@section('title', 'Anular baja de inventario | Santo Remedio')
@section('page-title', 'Anular baja de inventario')
@section('page-subtitle', 'Devolver al inventario el stock retirado por una baja')

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

<div class="cancel-stock-layout">

    <section class="cancel-stock-main">

        <div class="compact-card">
            <div class="compact-header">
                <div>
                    <h2>
                        <i class="bi bi-x-octagon"></i>
                        Anular baja #{{ $bajaInventario->numero_baja ?? 'BAJ-' . str_pad($bajaInventario->id, 6, '0', STR_PAD_LEFT) }}
                    </h2>

                    <p>
                        Esta acción devolverá al inventario la cantidad retirada en esta baja.
                    </p>
                </div>

                <a href="{{ route('bajas-inventario.show', $bajaInventario) }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Volver
                </a>
            </div>

            <div class="detail-stat-grid">
                <div class="detail-stat-card detail-stat-main">
                    <span>Cantidad a devolver</span>
                    <strong>{{ $bajaInventario->cantidad }}</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Stock actual</span>
                    <strong>{{ $bajaInventario->stock_nuevo }}</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Stock después de anular</span>
                    <strong>{{ $bajaInventario->stock_anterior }}</strong>
                </div>

                <div class="detail-stat-card">
                    <span>Motivo original</span>
                    <strong>
                        @if ($bajaInventario->motivo === 'vencimiento')
                            Vencimiento
                        @elseif ($bajaInventario->motivo === 'danado')
                            Dañado
                        @elseif ($bajaInventario->motivo === 'perdido')
                            Perdido
                        @elseif ($bajaInventario->motivo === 'ajuste_autorizado')
                            Ajuste autorizado
                        @else
                            Otro
                        @endif
                    </strong>
                </div>
            </div>
        </div>

        <div class="detail-section-card">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-capsule"></i>
                    Producto afectado
                </h3>
            </div>

            <div class="detail-info-grid">
                <div class="detail-info-item detail-info-full">
                    <span>Producto</span>
                    <strong>{{ $bajaInventario->producto->nombre_comercial ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Nombre genérico</span>
                    <strong>{{ $bajaInventario->producto->nombre_generico ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Concentración</span>
                    <strong>{{ $bajaInventario->producto->concentracion ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Laboratorio</span>
                    <strong>{{ $bajaInventario->producto->laboratorio->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Lote</span>
                    <strong>{{ $bajaInventario->lote->numero_lote ?? 'Sin lote' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Vencimiento</span>
                    <strong>
                        {{ $bajaInventario->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-' }}
                    </strong>
                </div>

                <div class="detail-info-item">
                    <span>Sucursal</span>
                    <strong>{{ $bajaInventario->sucursal->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item">
                    <span>Registrado por</span>
                    <strong>{{ $bajaInventario->usuario->nombre ?? '-' }}</strong>
                </div>

                <div class="detail-info-item detail-info-full">
                    <span>Observación original</span>
                    <strong>{{ $bajaInventario->observacion ?? '-' }}</strong>
                </div>
            </div>
        </div>

    </section>

    <aside class="cancel-stock-side">

        <div class="cancel-warning-card">
            <div class="cancel-warning-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>

            <div>
                <h3>Acción delicada</h3>
                <p>
                    Al confirmar, el stock retirado será devuelto al inventario y se registrará el movimiento de reversión.
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

            <form method="POST" action="{{ route('bajas-inventario.anular.store', $bajaInventario) }}" id="form_anular_baja">
                @csrf

                <div class="form-group">
                    <label>Motivo de anulación *</label>
                    <textarea
                        name="motivo_anulacion"
                        rows="7"
                        required
                        placeholder="Explique por qué se anula esta baja"
                    >{{ old('motivo_anulacion') }}</textarea>

                    <small class="cancel-help-text">
                        Debe registrar un motivo claro para auditoría.
                    </small>
                </div>

                <div class="cancel-final-summary">
                    <div>
                        <span>Stock a devolver</span>
                        <strong>{{ $bajaInventario->cantidad }}</strong>
                    </div>

                    <div>
                        <span>Stock final</span>
                        <strong>{{ $bajaInventario->stock_anterior }}</strong>
                    </div>
                </div>

                <div class="cancel-stock-actions">
                    <a href="{{ route('bajas-inventario.show', $bajaInventario) }}" class="btn-secondary">
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
document.getElementById('form_anular_baja').addEventListener('submit', function (event) {
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
        title: '¿Anular baja de inventario?',
        text: 'Esta acción devolverá el stock retirado al inventario.',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular baja',
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