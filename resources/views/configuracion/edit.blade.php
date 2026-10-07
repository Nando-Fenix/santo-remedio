@extends('layouts.app')

@section('title', 'Configuración | Santo Remedio')
@section('page-title', 'Configuración')
@section('page-subtitle', 'Datos generales de la farmacia')

@section('content')

<div class="config-page">

    <div class="compact-card config-header-card">
        <div>
            <h2>
                <i class="bi bi-gear"></i>
                Configuración general
            </h2>

            <p>
                Estos datos se usarán en recibos, reportes, comprobantes y documentos del sistema.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Volver
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success config-alert">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-danger config-alert">
            <strong>
                <i class="bi bi-exclamation-triangle"></i>
                Revisa los datos ingresados.
            </strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracion.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="config-layout">

            <div class="compact-card compact-form config-main-card">
                <div class="compact-header">
                    <div>
                        <h3>
                            <i class="bi bi-shop"></i>
                            Datos de la farmacia
                        </h3>
                        <p>Información principal que aparecerá en documentos del sistema.</p>
                    </div>
                </div>

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

                <div class="form-group">
                    <label>Mensaje para recibos</label>
                    <textarea
                        name="mensaje_recibo"
                        rows="4"
                        placeholder="Ej: Gracias por su compra. Vuelva pronto."
                    >{{ old('mensaje_recibo', $configuracion->mensaje_recibo) }}</textarea>
                </div>
            </div>

            <div class="compact-card compact-form config-logo-card">
                <div class="compact-header">
                    <div>
                        <h3>
                            <i class="bi bi-image"></i>
                            Logo y vista previa
                        </h3>
                        <p>Logo usado en recibos y documentos imprimibles.</p>
                    </div>
                </div>

                <div class="config-logo-preview">
                    @if ($configuracion->logo)
                        <img
                            src="{{ asset('storage/' . $configuracion->logo) }}"
                            alt="Logo de farmacia"
                        >
                    @else
                        <div class="config-logo-empty">
                            <i class="bi bi-image"></i>
                            <span>Sin logo</span>
                        </div>
                    @endif
                </div>

                @if ($configuracion->logo)
                    <label class="config-remove-logo">
                        <input type="checkbox" name="eliminar_logo" value="1">
                        <span>Quitar logo actual</span>
                    </label>
                @endif

                <div class="form-group">
                    <label>Subir nuevo logo</label>
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp">

                    <small>
                        Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.
                    </small>
                </div>

                <div class="config-receipt-preview">
                    <span>Vista referencial</span>

                    <div class="config-receipt-box">
                        <strong>{{ old('nombre_farmacia', $configuracion->nombre_farmacia) ?: 'Santo Remedio' }}</strong>
                        <small>{{ old('direccion', $configuracion->direccion) ?: 'Dirección de la farmacia' }}</small>
                        <small>Tel: {{ old('telefono', $configuracion->telefono) ?: '---' }}</small>

                        <hr>

                        <p>
                            {{ old('mensaje_recibo', $configuracion->mensaje_recibo) ?: 'Gracias por su compra.' }}
                        </p>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        Guardar configuración
                    </button>

                    <a href="{{ route('dashboard') }}" class="btn-secondary">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>
                </div>
            </div>

        </div>
    </form>

</div>

@endsection