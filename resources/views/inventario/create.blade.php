@extends('layouts.app')

@section('title', 'Entrada de inventario | Santo Remedio')
@section('page-title', 'Entrada de inventario')
@section('page-subtitle', 'Registro inicial o ingreso manual de stock')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Registrar entrada de inventario</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Use esta pantalla para cargar stock inicial o registrar ingreso manual de productos.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert-danger">
            <strong>Revise los siguientes errores:</strong>
            <ul style="margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventario.store') }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Producto *</label>
                <select name="producto_id">
                    <option value="">Seleccione un producto</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id }}" @selected(old('producto_id') == $producto->id)>
                            {{ $producto->nombre_comercial }}
                            @if($producto->concentracion)
                                - {{ $producto->concentracion }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Sucursal *</label>
                <select name="sucursal_id">
                    <option value="">Seleccione una sucursal</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" @selected(old('sucursal_id') == $sucursal->id)>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Número de lote</label>
                <input
                    type="text"
                    name="numero_lote"
                    value="{{ old('numero_lote') }}"
                    placeholder="Ej: A001"
                >
            </div>

            <div class="form-group">
                <label>Fecha de vencimiento</label>
                <input
                    type="date"
                    name="fecha_vencimiento"
                    value="{{ old('fecha_vencimiento') }}"
                >
            </div>

            <div class="form-group">
                <label>Cantidad a ingresar *</label>
                <input
                    type="number"
                    min="1"
                    name="cantidad"
                    value="{{ old('cantidad', 1) }}"
                >
            </div>

            <div class="form-group">
                <label>Stock mínimo *</label>
                <input
                    type="number"
                    min="0"
                    name="stock_minimo"
                    value="{{ old('stock_minimo', 0) }}"
                >
            </div>
        </div>

        <div class="form-group" style="margin-top: 18px;">
            <label>Motivo / observación</label>
            <textarea name="motivo" rows="4" placeholder="Ej: Entrada inicial de inventario">{{ old('motivo') }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">
                Guardar entrada
            </button>

            <a href="{{ route('inventario.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>

</div>

@endsection