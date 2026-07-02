@extends('layouts.app')

@section('title', 'Registrar egreso | Santo Remedio')
@section('page-title', 'Registrar egreso')
@section('page-subtitle', 'Gastos diarios autorizados de caja')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Nuevo egreso de caja</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Sucursal: <strong>{{ $sucursal->nombre }}</strong>.
            Caja abierta desde: <strong>{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</strong>
        </p>
    </div>

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

    <form method="POST" action="{{ route('caja.egreso.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Monto del egreso *</label>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="monto"
                    value="{{ old('monto') }}"
                    placeholder="Ej: 25"
                    required
                >
            </div>

            <div class="form-group">
                <label>Tipo de egreso sugerido</label>
                <select onchange="document.getElementById('descripcion').value = this.value">
                    <option value="">Seleccione si corresponde</option>
                    <option value="Almuerzo autorizado">Almuerzo autorizado</option>
                    <option value="Transporte">Transporte</option>
                    <option value="Compra menor">Compra menor</option>
                    <option value="Gasto operativo">Gasto operativo</option>
                    <option value="Otro gasto autorizado">Otro gasto autorizado</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 18px;">
            <label>Motivo / descripción *</label>
            <textarea
                id="descripcion"
                name="descripcion"
                rows="4"
                placeholder="Ej: Almuerzo autorizado por administración"
                required
            >{{ old('descripcion') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            @if (auth()->user()->tienePermiso('registrar_egreso'))
                <button type="submit" class="btn-primary">
                    Guardar egreso
                </button>
            @endif

            <a href="{{ route('caja.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
    @endif
</div>

@endsection