@extends('layouts.app')

@section('title', 'Editar proveedor | Santo Remedio')
@section('page-title', 'Editar proveedor')
@section('page-subtitle', 'Actualizar información del proveedor')

@section('content')

@if (!auth()->user()->tienePermiso('editar_proveedor'))
    <div class="alert-danger">
        No tiene permiso para editar proveedores.
    </div>
@else

<form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="provider-form">
    @csrf
    @method('PUT')

    <div class="provider-layout">

        <section class="provider-main">

            <div class="provider-header-card">
                <div>
                    <h2>
                        <i class="bi bi-truck"></i>
                        Editar proveedor
                    </h2>

                    <p>
                        Actualice los datos del proveedor para compras, productos, pagos pendientes y control de deudas.
                    </p>
                </div>
            </div>

            <div class="provider-card">
                <div class="provider-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-building"></i>
                            Datos principales
                        </h3>
                        <small>Nombre comercial y datos de contacto</small>
                    </div>
                </div>

                <div class="provider-grid">
                    <div class="form-group provider-full">
                        <label>Nombre del proveedor *</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre', $proveedor->nombre) }}"
                            required
                        >

                        @error('nombre')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Persona de contacto</label>
                        <input
                            type="text"
                            name="contacto"
                            value="{{ old('contacto', $proveedor->contacto) }}"
                        >

                        @error('contacto')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input
                            type="text"
                            name="telefono"
                            value="{{ old('telefono', $proveedor->telefono) }}"
                        >

                        @error('telefono')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

        </section>

        <aside class="provider-summary">

            <div class="provider-summary-card">
                <h3>
                    <i class="bi bi-geo-alt"></i>
                    Dirección
                </h3>

                <div class="form-group">
                    <label>Dirección</label>
                    <textarea
                        name="direccion"
                        rows="5"
                        placeholder="Ej. Av. Principal #123, zona central"
                    >{{ old('direccion', $proveedor->direccion) }}</textarea>

                    @error('direccion')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="provider-summary-card">
                <h3>
                    <i class="bi bi-toggle-on"></i>
                    Estado
                </h3>

                <div class="form-group">
                    <label>Estado *</label>
                    <select name="estado" required>
                        <option value="activo" {{ old('estado', $proveedor->estado) === 'activo' ? 'selected' : '' }}>
                            Activo
                        </option>

                        <option value="inactivo" {{ old('estado', $proveedor->estado) === 'inactivo' ? 'selected' : '' }}>
                            Inactivo
                        </option>
                    </select>

                    @error('estado')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="provider-info-card">
                <div class="provider-info-icon">
                    <i class="bi bi-bag-check"></i>
                </div>

                <div>
                    <strong>Uso en compras</strong>
                    <span>
                        Si el proveedor está inactivo, su historial se conserva, pero ya no debería usarse en nuevas compras.
                    </span>
                </div>
            </div>

            <div class="provider-actions">
                @if (auth()->user()->tienePermiso('ver_proveedores'))
                    <a href="{{ route('proveedores.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('editar_proveedor'))
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