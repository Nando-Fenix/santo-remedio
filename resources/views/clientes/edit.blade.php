@extends('layouts.app')

@section('title', 'Editar cliente | Santo Remedio')
@section('page-title', 'Editar cliente')
@section('page-subtitle', 'Actualizar información del cliente')

@section('content')

@if (!auth()->user()->tienePermiso('editar_cliente'))
    <div class="alert-danger">
        No tiene permiso para editar clientes.
    </div>
@else

<form method="POST" action="{{ route('clientes.update', $cliente) }}" class="client-form">
    @csrf
    @method('PUT')

    <div class="client-layout">

        <section class="client-main">

            <div class="client-header-card">
                <div>
                    <h2>
                        <i class="bi bi-person-gear"></i>
                        Editar cliente
                    </h2>

                    <p>
                        Actualice los datos del cliente para mantener correcto su historial de ventas y descuentos.
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
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre', $cliente->nombre) }}"
                            required
                        >

                        @error('nombre')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>CI / NIT</label>
                        <input
                            type="text"
                            name="ci_nit"
                            value="{{ old('ci_nit', $cliente->ci_nit) }}"
                        >

                        @error('ci_nit')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input
                            type="text"
                            name="telefono"
                            value="{{ old('telefono', $cliente->telefono) }}"
                        >

                        @error('telefono')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group client-full">
                        <label>Dirección</label>
                        <input
                            type="text"
                            name="direccion"
                            value="{{ old('direccion', $cliente->direccion) }}"
                        >

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
                    Tipo y estado
                </h3>

                <div class="form-group">
                    <label>Tipo de cliente *</label>
                    <select name="tipo_cliente" required>
                        <option value="normal" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="frecuente" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'frecuente' ? 'selected' : '' }}>Frecuente</option>
                        <option value="mayorista" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'mayorista' ? 'selected' : '' }}>Mayorista</option>
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
                        value="{{ old('descuento_default', $cliente->descuento_default) }}"
                        min="0"
                        max="100"
                        step="0.01"
                    >

                    @error('descuento_default')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label>Estado *</label>
                    <select name="estado" required>
                        <option value="activo" {{ old('estado', $cliente->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $cliente->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>

                    @error('estado')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="client-help-card">
                    <i class="bi bi-lightbulb"></i>
                    <span>
                        Un cliente inactivo no debería usarse para nuevas ventas, pero su historial se conserva.
                    </span>
                </div>
            </div>

            <div class="client-info-card">
                <div class="client-info-icon">
                    <i class="bi bi-receipt"></i>
                </div>

                <div>
                    <strong>Historial del cliente</strong>
                    <span>
                        Los cambios no eliminan ventas anteriores ni reportes relacionados.
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

                @if (auth()->user()->tienePermiso('editar_cliente'))
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Actualizar
                    </button>
                @endif
            </div>

        </aside>

    </div>
</form>

@endif

@endsection