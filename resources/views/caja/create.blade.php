@extends('layouts.app')

@section('title', 'Abrir caja | Santo Remedio')
@section('page-title', 'Abrir caja')
@section('page-subtitle', 'Inicio de turno y monto inicial')

@section('content')

@if (!auth()->user()->tienePermiso('abrir_caja'))
    <div class="alert-danger">
        No tiene permiso para abrir caja.
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

<form method="POST" action="{{ route('caja.store') }}" id="form_abrir_caja">
    @csrf

    <div class="cash-open-layout">

        <section class="cash-open-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-unlock"></i>
                            Abrir caja
                        </h2>

                        <p>
                            Sucursal actual:
                            <strong>{{ $sucursal->nombre }}</strong>
                        </p>
                    </div>

                    <a href="{{ route('caja.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="cash-open-info-grid">
                    <div>
                        <span>Sucursal</span>
                        <strong>{{ $sucursal->nombre }}</strong>
                    </div>

                    <div>
                        <span>Usuario</span>
                        <strong>{{ auth()->user()->nombre }}</strong>
                    </div>

                    <div>
                        <span>Fecha</span>
                        <strong>{{ now()->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>

            <div class="cash-open-help-card">
                <div>
                    <i class="bi bi-info-circle"></i>
                </div>

                <section>
                    <h3>Antes de abrir caja</h3>

                    <ul>
                        <li>Seleccione el turno correcto.</li>
                        <li>Registre el efectivo inicial disponible.</li>
                        <li>Use la observación si necesita dejar una aclaración.</li>
                        <li>Después de abrir caja, ya podrá registrar ventas y movimientos.</li>
                    </ul>
                </section>
            </div>

        </section>

        <aside class="cash-open-side">

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-cash-stack"></i>
                        Datos de apertura
                    </h3>
                </div>

                <div class="form-group">
                    <label>Turno *</label>
                    <select name="turno_id" required>
                        <option value="">Seleccione un turno</option>

                        @foreach ($turnos as $turno)
                            <option value="{{ $turno->id }}" @selected(old('turno_id') == $turno->id)>
                                {{ $turno->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Monto inicial *</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="monto_inicial"
                        value="{{ old('monto_inicial', 0) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea
                        name="observacion"
                        rows="5"
                        placeholder="Opcional"
                    >{{ old('observacion') }}</textarea>
                </div>

                <div class="cash-open-actions">
                    <a href="{{ route('caja.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-unlock"></i>
                        Abrir caja
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
document.getElementById('form_abrir_caja').addEventListener('submit', function (event) {
    event.preventDefault();

    const turno = document.querySelector('select[name="turno_id"]').value;
    const montoInicial = Number(document.querySelector('input[name="monto_inicial"]').value || 0);

    if (!turno) {
        Swal.fire({
            icon: 'warning',
            title: 'Turno requerido',
            text: 'Debe seleccionar un turno para abrir caja.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (montoInicial < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'El monto inicial no puede ser negativo.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'question',
        title: '¿Abrir caja?',
        text: 'Se iniciará el control de caja para este turno.',
        showCancelButton: true,
        confirmButtonText: 'Sí, abrir caja',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
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