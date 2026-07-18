@extends('layouts.app')

@section('title', 'Anular baja de inventario | Santo Remedio')
@section('page-title', 'Anular baja de inventario')
@section('page-subtitle', 'Devolver al inventario el stock retirado por una baja')

@section('content')

<div class="card">

    <h2 style="margin-top: 0; color: #991B1B;">Anular baja de inventario</h2>

    <p style="color: #6B7280;">
        Esta acción devolverá al inventario la cantidad retirada en esta baja.
    </p>

    <div class="card" style="background: #FAFAFA; margin-bottom: 22px;">
        <p>
            <strong>Producto:</strong>
            {{ $bajaInventario->producto->nombre_comercial ?? '-' }}
            <br>

            <strong>Lote:</strong>
            {{ $bajaInventario->lote->numero_lote ?? 'Sin lote' }}
            <br>

            <strong>Cantidad retirada:</strong>
            {{ $bajaInventario->cantidad }}
            <br>

            <strong>Motivo original:</strong>
            {{ $bajaInventario->motivo }}
        </p>
    </div>

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

    <form method="POST" action="{{ route('bajas-inventario.anular.store', $bajaInventario) }}" id="form_anular_baja">
        @csrf

        <div class="form-group">
            <label>Motivo de anulación *</label>
            <textarea
                name="motivo_anulacion"
                rows="4"
                required
                placeholder="Explique por qué se anula esta baja"
            >{{ old('motivo_anulacion') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 16px;">
            <button type="submit" class="btn-danger">
                Confirmar anulación
            </button>

            <a href="{{ route('bajas-inventario.show', $bajaInventario) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>

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