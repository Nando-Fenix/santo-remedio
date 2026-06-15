@extends('layouts.app')

@section('title', 'Editar proveedor | Santo Remedio')
@section('page-title', 'Editar proveedor')
@section('page-subtitle', 'Actualizar información del proveedor')

@section('content')

<div class="card">
    <form method="POST" action="{{ route('proveedores.update', $proveedor) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre del proveedor *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required>
                @error('nombre')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Persona de contacto</label>
                <input type="text" name="contacto" value="{{ old('contacto', $proveedor->contacto) }}">
                @error('contacto')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}">
                @error('telefono')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="activo" {{ old('estado', $proveedor->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('estado', $proveedor->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('estado')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion', $proveedor->direccion) }}">
                @error('direccion')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">
                Actualizar proveedor
            </button>

            <a href="{{ route('proveedores.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection