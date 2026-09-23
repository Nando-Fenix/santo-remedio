@extends('layouts.app')

@section('title', 'Nueva sucursal | Santo Remedio')
@section('page-title', 'Nueva sucursal')
@section('page-subtitle', 'Registro de sucursal')

@section('content')

<div class="sucursal-page">

    <div class="compact-card sucursal-header">
        <div>
            <h2>
                <i class="bi bi-building-add"></i>
                Nueva sucursal
            </h2>

            <p>Registra una sucursal para ventas, compras, caja e inventario.</p>
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
        <form method="POST" action="{{ route('sucursales.store') }}">
            @csrf

            <div class="sucursal-form-grid">
                <div class="form-group">
                    <label>Nombre de la sucursal</label>
                    <input
                        type="text"
                        name="nombre"
                        value="{{ old('nombre') }}"
                        placeholder="Ej: Sucursal Central"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="activo" @selected(old('estado', 'activo') === 'activo')>
                            Activa
                        </option>
                        <option value="inactivo" @selected(old('estado') === 'inactivo')>
                            Inactiva
                        </option>
                    </select>
                </div>

                <div class="form-group sucursal-form-full">
                    <label>Dirección</label>
                    <input
                        type="text"
                        name="direccion"
                        value="{{ old('direccion') }}"
                        placeholder="Dirección de la sucursal"
                    >
                </div>
            </div>

            <div class="sucursal-form-actions">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-save"></i>
                    Guardar
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