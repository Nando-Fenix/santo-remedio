@extends('layouts.app')

@section('title', 'Editar sucursal | Santo Remedio')
@section('page-title', 'Editar sucursal')
@section('page-subtitle', 'Actualizar datos de sucursal')

@section('content')

<div class="sucursal-page">

    <div class="compact-card sucursal-header">
        <div>
            <h2>
                <i class="bi bi-building-gear"></i>
                Editar sucursal
            </h2>

            <p>
                Actualizando:
                <strong>{{ $sucursal->nombre }}</strong>
            </p>
        </div>

        <a href="{{ route('sucursales.index') }}" class="btn-secondary btn-mini">
            <i class="bi bi-arrow-left"></i>
            Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert-danger">
            <strong>
                <i class="bi bi-exclamation-triangle"></i>
                Revisa los datos ingresados.
            </strong>

            <ul style="margin-bottom:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="compact-card sucursal-form-card">
        <form method="POST" action="{{ route('sucursales.update', $sucursal) }}">
            @csrf
            @method('PUT')

            <div class="sucursal-form-grid">
                <div class="form-group">
                    <label>Nombre de la sucursal</label>
                    <input
                        type="text"
                        name="nombre"
                        value="{{ old('nombre', $sucursal->nombre) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="activo" @selected(old('estado', $sucursal->estado) === 'activo')>
                            Activa
                        </option>
                        <option value="inactivo" @selected(old('estado', $sucursal->estado) === 'inactivo')>
                            Inactiva
                        </option>
                    </select>
                </div>

                <div class="form-group sucursal-form-full">
                    <label>Dirección</label>
                    <input
                        type="text"
                        name="direccion"
                        value="{{ old('direccion', $sucursal->direccion) }}"
                        placeholder="Dirección de la sucursal"
                    >
                </div>
            </div>

            <div class="sucursal-form-actions">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-save"></i>
                    Guardar cambios
                </button>

                <a href="{{ route('sucursales.index') }}" class="btn-secondary">
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>

</div>

@endsection