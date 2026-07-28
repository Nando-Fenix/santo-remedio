@extends('layouts.app')

@section('title', 'Entrada de inventario | Santo Remedio')
@section('page-title', 'Entrada de inventario')
@section('page-subtitle', 'Registro inicial o ingreso manual de stock')

@section('content')

@if (!auth()->user()->tienePermiso('ajustar_inventario'))
    <div class="alert-danger">
        No tiene permiso para registrar entradas de inventario.
    </div>
@else

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

<form method="POST" action="{{ route('inventario.store') }}" class="inventory-form">
    @csrf

    <div class="inventory-layout">

        <section class="inventory-main">

            <div class="inventory-header-card">
                <div>
                    <h2>
                        <i class="bi bi-box-arrow-in-down"></i>
                        Registrar entrada de inventario
                    </h2>

                    <p>
                        Use esta pantalla para cargar stock inicial o registrar ingresos manuales de productos.
                    </p>
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-capsule"></i>
                            Producto y sucursal
                        </h3>
                        <small>Seleccione el producto que ingresará al inventario</small>
                    </div>
                </div>

                <div class="inventory-grid">
                    <div class="form-group inventory-full">
                        <label>Producto *</label>
                        <select name="producto_id" required>
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

                    <div class="form-group inventory-full">
                        <label>Sucursal *</label>
                        <select name="sucursal_id" required>
                            <option value="">Seleccione una sucursal</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" @selected(old('sucursal_id') == $sucursal->id)>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-upc-scan"></i>
                            Lote y vencimiento
                        </h3>
                        <small>Datos útiles para control FEFO y alertas de vencimiento</small>
                    </div>
                </div>

                <div class="inventory-grid">
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
                </div>
            </div>

        </section>

        <aside class="inventory-summary">

            <div class="inventory-summary-card">
                <h3>
                    <i class="bi bi-boxes"></i>
                    Cantidades
                </h3>

                <div class="inventory-side-grid">
                    <div class="form-group">
                        <label>Cantidad a ingresar *</label>
                        <input
                            type="number"
                            min="1"
                            name="cantidad"
                            value="{{ old('cantidad', 1) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Stock mínimo *</label>
                        <input
                            type="number"
                            min="0"
                            name="stock_minimo"
                            value="{{ old('stock_minimo', 0) }}"
                            required
                        >
                    </div>
                </div>

                <div class="inventory-help-card">
                    <i class="bi bi-lightbulb"></i>
                    <span>
                        El stock mínimo ayuda a detectar productos que necesitan reposición.
                    </span>
                </div>
            </div>

            <div class="inventory-summary-card">
                <h3>
                    <i class="bi bi-card-text"></i>
                    Motivo
                </h3>

                <div class="form-group">
                    <label>Motivo / observación</label>
                    <textarea
                        name="motivo"
                        rows="5"
                        placeholder="Ej: Entrada inicial de inventario"
                    >{{ old('motivo') }}</textarea>
                </div>
            </div>

            <div class="inventory-actions">
                @if (auth()->user()->tienePermiso('ver_inventario'))
                    <a href="{{ route('inventario.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('ajustar_inventario'))
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Guardar entrada
                    </button>
                @endif
            </div>

        </aside>

    </div>
</form>
@endif

@endsection