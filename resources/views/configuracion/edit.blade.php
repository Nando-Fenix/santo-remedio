@extends('layouts.app')

@section('title', 'Configuración | Santo Remedio')
@section('page-title', 'Configuración')
@section('page-subtitle', 'Datos generales de la farmacia')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <h2 style="margin-top:0; color:#4C1D95;">Configuración general</h2>

    <p style="color:#6B7280;">
        Estos datos se usarán después en recibos, reportes, comprobantes y documentos del sistema.
    </p>
</div>

@if (session('success'))
    <div class="alert-success" style="margin-bottom: 22px;">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert-danger" style="margin-bottom: 22px;">
        <strong>Revisa los datos ingresados.</strong>
        <ul style="margin-bottom:0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ route('configuracion.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Nombre de la farmacia</label>
                <input
                    type="text"
                    name="nombre_farmacia"
                    value="{{ old('nombre_farmacia', $configuracion->nombre_farmacia) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label>NIT</label>
                <input
                    type="text"
                    name="nit"
                    value="{{ old('nit', $configuracion->nit) }}"
                    placeholder="Ej: 123456789"
                >
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input
                    type="text"
                    name="telefono"
                    value="{{ old('telefono', $configuracion->telefono) }}"
                    placeholder="Ej: 70000000"
                >
            </div>

            <div class="form-group">
                <label>Ciudad</label>
                <input
                    type="text"
                    name="ciudad"
                    value="{{ old('ciudad', $configuracion->ciudad) }}"
                    placeholder="Ej: Patacamaya"
                >
            </div>

            <div class="form-group">
                <label>Moneda</label>
                <input
                    type="text"
                    name="moneda"
                    value="{{ old('moneda', $configuracion->moneda) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <input
                    type="text"
                    name="direccion"
                    value="{{ old('direccion', $configuracion->direccion) }}"
                    placeholder="Dirección de la farmacia"
                >
            </div>
        </div>

        <div class="form-group" style="margin-top:16px;">
            <label>Mensaje para recibos</label>
            <textarea
                name="mensaje_recibo"
                rows="4"
                placeholder="Ej: Gracias por su compra. Vuelva pronto."
            >{{ old('mensaje_recibo', $configuracion->mensaje_recibo) }}</textarea>
        </div>

        <div class="form-group" style="margin-top:16px;">
            <label>Logo de la farmacia</label>

            @if ($configuracion->logo)
                <div style="margin-bottom:12px;">
                    <img
                        src="{{ asset('storage/' . $configuracion->logo) }}"
                        alt="Logo de farmacia"
                        style="max-height:90px; max-width:220px; border:1px solid #E5E7EB; border-radius:10px; padding:8px; background:white;"
                    >

                    <label style="display:flex; align-items:center; gap:8px; margin-top:10px; color:#991B1B;">
                        <input type="checkbox" name="eliminar_logo" value="1">
                        Quitar logo actual
                    </label>
                </div>
            @endif

            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp">

            <small style="display:block; margin-top:6px; color:#6B7280;">
                Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.
            </small>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px;">
            <button type="submit" class="btn-primary">
                Guardar configuración
            </button>

            <a href="{{ route('dashboard') }}" class="btn-secondary">
                Volver
            </a>
        </div>
    </form>
</div>

@endsection