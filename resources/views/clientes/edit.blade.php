@extends('layouts.app')

@section('title', 'Editar cliente | Santo Remedio')
@section('page-title', 'Editar cliente')
@section('page-subtitle', 'Actualizar información del cliente')

@section('content')

<div class="card">
    <form method="POST" action="{{ route('clientes.update', $cliente) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre del cliente *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required>
                @error('nombre')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>CI / NIT</label>
                <input type="text" name="ci_nit" value="{{ old('ci_nit', $cliente->ci_nit) }}">
                @error('ci_nit')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}">
                @error('telefono')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Tipo de cliente</label>
                <select name="tipo_cliente" required>
                    <option value="normal" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="frecuente" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'frecuente' ? 'selected' : '' }}>Frecuente</option>
                    <option value="mayorista" {{ old('tipo_cliente', $cliente->tipo_cliente) === 'mayorista' ? 'selected' : '' }}>Mayorista</option>
                </select>
                @error('tipo_cliente')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Descuento por defecto (%)</label>
                <input type="number" name="descuento_default" value="{{ old('descuento_default', $cliente->descuento_default) }}" min="0" max="100" step="0.01">
                @error('descuento_default')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="activo" {{ old('estado', $cliente->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('estado', $cliente->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('estado')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}">
                @error('direccion')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">
                Actualizar cliente
            </button>

            <a href="{{ route('clientes.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection