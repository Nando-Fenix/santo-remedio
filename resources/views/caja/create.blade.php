@extends('layouts.app')

@section('title', 'Abrir caja | Santo Remedio')
@section('page-title', 'Abrir caja')
@section('page-subtitle', 'Inicio de turno y monto inicial')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Abrir caja</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Sucursal: <strong>{{ $sucursal->nombre }}</strong>.
            Registre el turno y el monto inicial disponible.
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

    <form method="POST" action="{{ route('caja.store') }}">
        @csrf

        <div class="form-grid">
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
        </div>

        <div class="form-group" style="margin-top: 18px;">
            <label>Observación</label>
            <textarea name="observacion" rows="4" placeholder="Opcional">{{ old('observacion') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">
                Abrir caja
            </button>

            <a href="{{ route('caja.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>

</div>

@endsection