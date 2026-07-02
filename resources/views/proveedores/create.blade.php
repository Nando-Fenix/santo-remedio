@extends('layouts.app')

@section('title', 'Nuevo proveedor | Santo Remedio')
@section('page-title', 'Nuevo proveedor')
@section('page-subtitle', 'Registrar datos básicos del proveedor')

@section('content')

<div class="card">

    @if (!auth()->user()->tienePermiso('crear_proveedor'))
        <div class="alert-danger">
            No tiene permiso para crear proveedores.
        </div>
    @else

    <form method="POST" action="{{ route('proveedores.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre del proveedor *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required>
                @error('nombre')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label>Persona de contacto</label>
                <input type="text" name="contacto" value="{{ old('contacto') }}">
                @error('contacto')
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

            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion') }}">
                @error('direccion')
                    <small class="error">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            @if (auth()->user()->tienePermiso('crear_proveedor'))
                <button type="submit" class="btn-primary">
                    Guardar proveedor
                </button>
            @endif

            @if (auth()->user()->tienePermiso('ver_proveedores'))
                <a href="{{ route('proveedores.index') }}" class="btn-secondary">
                    Cancelar
                </a>
            @endif
        </div>
    </form>

    @endif
</div>

@endsection