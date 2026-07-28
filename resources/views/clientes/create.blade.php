@extends('layouts.app')

@section('title', 'Nuevo cliente | Santo Remedio')
@section('page-title', 'Nuevo cliente')
@section('page-subtitle', 'Registrar datos básicos del cliente')

@section('content')

@if (!auth()->user()->tienePermiso('crear_cliente'))
    <div class="alert-danger">
        No tiene permiso para crear clientes.
    </div>
@else

<form method="POST" action="{{ route('clientes.store') }}" class="client-form">
    @csrf

    <div class="client-layout">

        <section class="client-main">

            <div class="client-header-card">
                <div>
                    <h2>
                        <i class="bi bi-person-plus"></i>
                        Registrar cliente
                    </h2>

                    <p>
                        Guarde los datos básicos del cliente para ventas, historial y descuentos frecuentes.
                    </p>
                </div>
            </div>

            <div class="client-card">
                <div class="client-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-person-vcard"></i>
                            Datos principales
                        </h3>
                        <small>Nombre, documento y contacto</small>
                    </div>
                </div>

                <div class="client-grid">
                    <div class="form-group client-full">
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

                    <div class="form-group client-full">
                        <label>Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion') }}">

                        @error('direccion')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

        </section>

        <aside class="client-summary">

            <div class="client-summary-card">
                <h3>
                    <i class="bi bi-award"></i>
                    Tipo de cliente
                </h3>

                <div class="form-group">
                    <label>Tipo de cliente *</label>
                    <select name="tipo_cliente" required>
                        <option value="normal" {{ old('tipo_cliente') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="frecuente" {{ old('tipo_cliente') === 'frecuente' ? 'selected' : '' }}>Frecuente</option>
                        <option value="mayorista" {{ old('tipo_cliente') === 'mayorista' ? 'selected' : '' }}>Mayorista</option>
                    </select>

                    @error('tipo_cliente')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label>Descuento por defecto (%)</label>
                    <input
                        type="number"
                        name="descuento_default"
                        value="{{ old('descuento_default', 0) }}"
                        min="0"
                        max="100"
                        step="0.01"
                    >

                    @error('descuento_default')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="client-help-card">
                    <i class="bi bi-lightbulb"></i>
                    <span>
                        El descuento se puede usar para clientes frecuentes o mayoristas.
                    </span>
                </div>
            </div>

            <div class="client-info-card">
                <div class="client-info-icon">
                    <i class="bi bi-receipt"></i>
                </div>

                <div>
                    <strong>Uso en ventas</strong>
                    <span>
                        Al seleccionar este cliente en una venta, sus datos quedarán registrados en el historial.
                    </span>
                </div>
            </div>

            <div class="client-actions">
                @if (auth()->user()->tienePermiso('ver_clientes'))
                    <a href="{{ route('clientes.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('crear_cliente'))
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Guardar cliente
                    </button>
                @endif
            </div>

        </aside>

    </div>
</form>

@endif

@endsection