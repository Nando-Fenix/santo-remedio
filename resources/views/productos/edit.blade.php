@extends('layouts.app')

@section('title', 'Editar producto | Santo Remedio')
@section('page-title', 'Editar producto')
@section('page-subtitle', 'Modificar datos generales del producto')

@section('content')

@if (!auth()->user()->tienePermiso('editar_producto'))
    <div class="alert-danger">
        No tiene permiso para editar productos.
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

<form method="POST" action="{{ route('productos.update', $producto) }}" class="product-form">
    @csrf
    @method('PUT')

    <div class="product-layout">

        <section class="product-main">

            <div class="product-header-card">
                <div>
                    <h2>
                        <i class="bi bi-pencil-square"></i>
                        Editar producto
                    </h2>

                    <p>
                        Modifique los datos generales. Los precios, códigos de barras y formas de venta se administran desde presentaciones.
                    </p>
                </div>
            </div>

            <div class="product-card">
                <div class="product-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-info-circle"></i>
                            Datos generales
                        </h3>
                        <small>Nombre, concentración y tipo de producto</small>
                    </div>
                </div>

                <div class="product-grid">
                    <div class="form-group">
                        <label>Nombre comercial *</label>
                        <input
                            type="text"
                            name="nombre_comercial"
                            value="{{ old('nombre_comercial', $producto->nombre_comercial) }}"
                            placeholder="Ej. Diclofenaco, Cepillo dental Colgate"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Nombre genérico</label>
                        <input
                            type="text"
                            name="nombre_generico"
                            value="{{ old('nombre_generico', $producto->nombre_generico) }}"
                        >
                    </div>

                    <div class="form-group">
                        <label>Concentración</label>
                        <input
                            type="text"
                            name="concentracion"
                            value="{{ old('concentracion', $producto->concentracion) }}"
                            placeholder="Ej. 500mg, 75mg/3ml, 250ml"
                        >
                    </div>

                    <div class="form-group">
                        <label for="tipo_producto">Tipo de producto *</label>
                        <select name="tipo_producto" id="tipo_producto" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="medicamento" {{ old('tipo_producto', $producto->tipo_producto) == 'medicamento' ? 'selected' : '' }}>Medicamento</option>
                            <option value="insumo_medico" {{ old('tipo_producto', $producto->tipo_producto) == 'insumo_medico' ? 'selected' : '' }}>Insumo médico</option>
                            <option value="producto_general" {{ old('tipo_producto', $producto->tipo_producto) == 'producto_general' ? 'selected' : '' }}>Producto general</option>
                            <option value="higiene" {{ old('tipo_producto', $producto->tipo_producto) == 'higiene' ? 'selected' : '' }}>Higiene</option>
                            <option value="bebe" {{ old('tipo_producto', $producto->tipo_producto) == 'bebe' ? 'selected' : '' }}>Bebé</option>
                            <option value="otro" {{ old('tipo_producto', $producto->tipo_producto) == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="product-card">
                <div class="product-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-folder2-open"></i>
                            Clasificación
                        </h3>
                        <small>Categoría, laboratorio o proveedor principal</small>
                    </div>
                </div>

                <div class="product-grid">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="categoria_id">
                            <option value="">Sin categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" @selected(old('categoria_id', $producto->categoria_id) == $categoria->id)>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Laboratorio / Marca</label>
                        <select name="laboratorio_id">
                            <option value="">Sin laboratorio / marca</option>
                            @foreach ($laboratorios as $laboratorio)
                                <option value="{{ $laboratorio->id }}" @selected(old('laboratorio_id', $producto->laboratorio_id) == $laboratorio->id)>
                                    {{ $laboratorio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group product-full">
                        <label>Proveedor principal</label>
                        <select name="proveedor_id">
                            <option value="">Sin proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" @selected(old('proveedor_id', $producto->proveedor_id) == $proveedor->id)>
                                    {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

        </section>

        <aside class="product-summary">

            <div class="product-summary-card">
                <h3>
                    <i class="bi bi-card-text"></i>
                    Descripción
                </h3>

                <div class="form-group">
                    <label>Descripción o uso</label>
                    <textarea
                        name="descripcion"
                        rows="6"
                        placeholder="Opcional"
                    >{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
            </div>

            <div class="product-info-card">
                <div class="product-info-icon">
                    <i class="bi bi-box-seam"></i>
                </div>

                <div>
                    <strong>Presentaciones</strong>
                    <span>
                        Aquí se modifican precios, códigos de barras y formas de venta del producto.
                    </span>
                </div>
            </div>

            <div class="product-actions product-actions-column">
                @if (auth()->user()->tienePermiso('ver_productos'))
                    <a href="{{ route('productos.presentaciones.index', $producto) }}" class="btn-secondary">
                        <i class="bi bi-boxes"></i>
                        Presentaciones
                    </a>
                @endif

                <div class="product-action-row">
                    @if (auth()->user()->tienePermiso('ver_productos'))
                        <a href="{{ route('productos.index') }}" class="btn-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Cancelar
                        </a>
                    @endif

                    @if (auth()->user()->tienePermiso('editar_producto'))
                        <button type="submit" class="btn-primary">
                            <i class="bi bi-check2-circle"></i>
                            Guardar
                        </button>
                    @endif
                </div>
            </div>

        </aside>

    </div>
</form>
@endif

@endsection