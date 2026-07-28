@extends('layouts.app')

@section('title', 'Nuevo producto | Santo Remedio')
@section('page-title', 'Nuevo producto')
@section('page-subtitle', 'Registro de producto y presentación principal')

@section('content')

@if (!auth()->user()->tienePermiso('crear_producto'))
    <div class="alert-danger">
        No tiene permiso para crear productos.
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

<form method="POST" action="{{ route('productos.store') }}" class="product-form">
    @csrf

    <div class="product-layout">

        <section class="product-main">

            <div class="product-header-card">
                <div>
                    <h2>
                        <i class="bi bi-capsule"></i>
                        Registrar producto
                    </h2>

                    <p>
                        Complete los datos principales para dejar el producto listo para vender, comprar o usar en inventario.
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
                        <label for="nombre_comercial">Nombre comercial *</label>
                        <input
                            type="text"
                            name="nombre_comercial"
                            id="nombre_comercial"
                            value="{{ old('nombre_comercial') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="nombre_generico">Nombre genérico</label>
                        <input
                            type="text"
                            name="nombre_generico"
                            id="nombre_generico"
                            value="{{ old('nombre_generico') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label for="concentracion">Concentración</label>
                        <input
                            type="text"
                            name="concentracion"
                            id="concentracion"
                            value="{{ old('concentracion') }}"
                            placeholder="Ej. 500mg, 75mg/3ml"
                        >
                    </div>

                    <div class="form-group">
                        <label for="tipo_producto">Tipo *</label>
                        <select name="tipo_producto" id="tipo_producto" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="medicamento" {{ old('tipo_producto') == 'medicamento' ? 'selected' : '' }}>Medicamento</option>
                            <option value="insumo_medico" {{ old('tipo_producto') == 'insumo_medico' ? 'selected' : '' }}>Insumo médico</option>
                            <option value="producto_general" {{ old('tipo_producto') == 'producto_general' ? 'selected' : '' }}>Producto general</option>
                            <option value="higiene" {{ old('tipo_producto') == 'higiene' ? 'selected' : '' }}>Higiene</option>
                            <option value="bebe" {{ old('tipo_producto') == 'bebe' ? 'selected' : '' }}>Bebé</option>
                            <option value="otro" {{ old('tipo_producto') == 'otro' ? 'selected' : '' }}>Otro</option>
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
                        <small>Categoría, laboratorio o proveedor relacionado</small>
                    </div>
                </div>

                <div class="product-grid">
                    <div class="form-group">
                        <label for="categoria_id">Categoría</label>
                        <select name="categoria_id" id="categoria_id">
                            <option value="">Sin categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="laboratorio_id">Laboratorio / Marca</label>
                        <select name="laboratorio_id" id="laboratorio_id">
                            <option value="">Sin laboratorio / marca</option>
                            @foreach ($laboratorios as $laboratorio)
                                <option value="{{ $laboratorio->id }}" {{ old('laboratorio_id') == $laboratorio->id ? 'selected' : '' }}>
                                    {{ $laboratorio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group product-full">
                        <label for="proveedor_id">Proveedor</label>
                        <select name="proveedor_id" id="proveedor_id">
                            <option value="">Sin proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="product-card">
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea
                        name="descripcion"
                        id="descripcion"
                        rows="3"
                        placeholder="Opcional"
                    >{{ old('descripcion') }}</textarea>
                </div>
            </div>

        </section>

        <aside class="product-summary">

            <div class="product-summary-card">
                <h3>
                    <i class="bi bi-box-seam"></i>
                    Presentación principal
                </h3>

                <div class="product-side-grid">
                    <div class="form-group product-side-full">
                        <label for="presentacion_id">Presentación *</label>
                        <select name="presentacion_id" id="presentacion_id" required>
                            <option value="">Seleccione presentación</option>
                            @foreach ($presentaciones as $presentacion)
                                <option value="{{ $presentacion->id }}" {{ old('presentacion_id') == $presentacion->id ? 'selected' : '' }}>
                                    {{ $presentacion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group product-side-full">
                        <label for="nombre_mostrado">Nombre mostrado en venta *</label>
                        <input
                            type="text"
                            name="nombre_mostrado"
                            id="nombre_mostrado"
                            value="{{ old('nombre_mostrado') }}"
                            placeholder="Ej. Diclofenaco 75mg/3ml Bagó - Ampolla"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="unidades_equivalentes">Unidades *</label>
                        <input
                            type="number"
                            name="unidades_equivalentes"
                            id="unidades_equivalentes"
                            value="{{ old('unidades_equivalentes', 1) }}"
                            min="1"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="codigo_barras">Código barras</label>
                        <input
                            type="text"
                            name="codigo_barras"
                            id="codigo_barras"
                            value="{{ old('codigo_barras') }}"
                            placeholder="Opcional"
                        >
                    </div>
                </div>

                <div class="product-help-card">
                    <i class="bi bi-lightbulb"></i>
                    <span>
                        Ejemplo: unidad = 1, blíster x10 = 10, caja x100 = 100.
                    </span>
                </div>
            </div>

            <div class="product-summary-card">
                <h3>
                    <i class="bi bi-cash-coin"></i>
                    Precios
                </h3>

                <div class="product-side-grid">
                    <div class="form-group">
                        <label for="precio_compra">Compra *</label>
                        <input
                            type="number"
                            step="0.01"
                            name="precio_compra"
                            id="precio_compra"
                            value="{{ old('precio_compra') }}"
                            min="0"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="precio_venta">Venta *</label>
                        <input
                            type="number"
                            step="0.01"
                            name="precio_venta"
                            id="precio_venta"
                            value="{{ old('precio_venta') }}"
                            min="0"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="product-actions">
                @if (auth()->user()->tienePermiso('ver_productos'))
                    <a href="{{ route('productos.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('crear_producto'))
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Guardar producto
                    </button>
                @endif
            </div>

        </aside>

    </div>
</form>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nombreComercial = document.getElementById('nombre_comercial');
    const concentracion = document.getElementById('concentracion');
    const laboratorio = document.getElementById('laboratorio_id');
    const presentacion = document.getElementById('presentacion_id');
    const nombreMostrado = document.getElementById('nombre_mostrado');

    function obtenerTextoSelect(select) {
        if (!select || !select.value) {
            return '';
        }

        return select.options[select.selectedIndex].text.trim();
    }

    function generarNombreMostrado() {
        if (nombreMostrado.value.trim() !== '') {
            return;
        }

        const partes = [];

        if (nombreComercial.value.trim()) {
            partes.push(nombreComercial.value.trim());
        }

        if (concentracion.value.trim()) {
            partes.push(concentracion.value.trim());
        }

        const laboratorioTexto = obtenerTextoSelect(laboratorio);
        if (laboratorioTexto && laboratorioTexto !== 'Sin laboratorio / marca') {
            partes.push(laboratorioTexto);
        }

        const presentacionTexto = obtenerTextoSelect(presentacion);

        let resultado = partes.join(' ');

        if (presentacionTexto && presentacionTexto !== 'Seleccione una presentación') {
            resultado += resultado ? ' - ' + presentacionTexto : presentacionTexto;
        }

        nombreMostrado.value = resultado;
    }

    [nombreComercial, concentracion, laboratorio, presentacion].forEach(function (elemento) {
        if (elemento) {
            elemento.addEventListener('change', generarNombreMostrado);
            elemento.addEventListener('blur', generarNombreMostrado);
        }
    });
});
</script>
@endsection