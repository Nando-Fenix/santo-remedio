@extends('layouts.app')

@section('title', 'Nuevo cliente | Santo Remedio')
@section('page-title', 'Nuevo cliente')
@section('page-subtitle', 'Registrar datos básicos del cliente')

@section('content')

<div class="card">
    @if (!auth()->user()->tienePermiso('crear_cliente'))
        <div class="alert-danger">
            No tiene permiso para crear clientes.
        </div>
    @else
    <form method="POST" action="{{ route('clientes.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre del cliente *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required>
                @error('nombre')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>CI / NIT</label>
                <input type="text" name="ci_nit" value="{{ old('ci_nit') }}">
                @error('ci_nit')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}">
                @error('telefono')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Tipo de cliente</label>
                <select name="tipo_cliente" required>
                    <option value="normal" {{ old('tipo_cliente') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="frecuente" {{ old('tipo_cliente') === 'frecuente' ? 'selected' : '' }}>Frecuente</option>
                    <option value="mayorista" {{ old('tipo_cliente') === 'mayorista' ? 'selected' : '' }}>Mayorista</option>
                </select>
                @error('tipo_cliente')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Descuento por defecto (%)</label>
                <input type="number" name="descuento_default" value="{{ old('descuento_default', 0) }}" min="0" max="100" step="0.01">
                @error('descuento_default')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion') }}">
                @error('direccion')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            @if (auth()->user()->tienePermiso('crear_cliente'))
                <button type="submit" class="btn-primary">
                    Guardar cliente
                </button>
            @endif

            @if (auth()->user()->tienePermiso('ver_clientes'))
                <a href="{{ route('clientes.index') }}" class="btn-secondary">
                    Cancelar
                </a>
            @endif
        </div>
    </form>

    @endif
</div>

@endsection