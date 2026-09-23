@extends('layouts.app')

@section('title', 'Registrar egreso | Santo Remedio')
@section('page-title', 'Registrar egreso')
@section('page-subtitle', 'Gastos diarios autorizados de caja')

@section('content')

@if (!auth()->user()->tienePermiso('registrar_egreso'))
    <div class="alert-danger">
        No tiene permiso para registrar egresos de caja.
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

<form method="POST" action="{{ route('caja.egreso.store') }}" id="form_registrar_egreso">
    @csrf

    <div class="cash-expense-layout">

        <section class="cash-expense-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-dash-circle"></i>
                            Nuevo egreso de caja
                        </h2>

                        <p>
                            Registre una salida de dinero autorizada de la caja actual.
                        </p>
                    </div>

                    <a href="{{ route('caja.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="cash-expense-info-grid">
                    <div>
                        <span>Sucursal</span>
                        <strong>{{ $sucursal->nombre }}</strong>
                    </div>

                    <div>
                        <span>Usuario</span>
                        <strong>{{ auth()->user()->nombre }}</strong>
                    </div>

                    <div>
                        <span>Caja abierta desde</span>
                        <strong>{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>

            <div class="cash-expense-help-card">
                <div>
                    <i class="bi bi-info-circle"></i>
                </div>

                <section>
                    <h3>Uso recomendado</h3>

                    <ul>
                        <li>Registre solo gastos autorizados.</li>
                        <li>El monto se descontará del efectivo esperado en caja.</li>
                        <li>Escriba un motivo claro para auditoría.</li>
                        <li>No use egresos para corregir ventas; use anulación, reembolso o cambio según corresponda.</li>
                    </ul>
                </section>
            </div>

        </section>

        <aside class="cash-expense-side">

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-cash-stack"></i>
                        Datos del egreso
                    </h3>
                </div>

                <div class="form-group">
                    <label>Monto del egreso *</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="monto"
                        id="monto_egreso"
                        value="{{ old('monto') }}"
                        placeholder="Ej: 25"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Tipo de egreso sugerido</label>
                    <select id="tipo_egreso_sugerido">
                        <option value="">Seleccione si corresponde</option>
                        <option value="Almuerzo autorizado">Almuerzo autorizado</option>
                        <option value="Transporte">Transporte</option>
                        <option value="Compra menor">Compra menor</option>
                        <option value="Gasto operativo">Gasto operativo</option>
                        <option value="Otro gasto autorizado">Otro gasto autorizado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Motivo / descripción *</label>
                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="7"
                        placeholder="Ej: Almuerzo autorizado por administración"
                        required
                    >{{ old('descripcion') }}</textarea>
                </div>

                <div class="cash-expense-preview">
                    <span>Egreso a registrar</span>
                    <strong id="egreso_preview">0.00 Bs</strong>
                </div>

                <div class="cash-expense-actions">
                    <a href="{{ route('caja.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Guardar
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
const formEgreso = document.getElementById('form_registrar_egreso');
const montoEgreso = document.getElementById('monto_egreso');
const egresoPreview = document.getElementById('egreso_preview');
const tipoEgresoSugerido = document.getElementById('tipo_egreso_sugerido');
const descripcionEgreso = document.getElementById('descripcion');

tipoEgresoSugerido.addEventListener('change', function () {
    if (this.value) {
        descripcionEgreso.value = this.value;
    }
});

montoEgreso.addEventListener('input', function () {
    const monto = Number(this.value || 0);
    egresoPreview.textContent = monto.toFixed(2) + ' Bs';
});

formEgreso.addEventListener('submit', function (event) {
    event.preventDefault();

    const monto = Number(montoEgreso.value || 0);
    const descripcion = descripcionEgreso.value.trim();

    if (monto <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Monto inválido',
            text: 'El monto del egreso debe ser mayor a 0.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (descripcion.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Descripción requerida',
            text: 'La descripción debe tener al menos 5 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'warning',
        title: '¿Registrar egreso?',
        text: 'Este monto se descontará del efectivo esperado en caja.',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});

montoEgreso.dispatchEvent(new Event('input'));
</script>

@endif

@endsection