@extends('layouts.app')

@section('title', 'Nuevo producto | Santo Remedio')
@section('page-title', 'Nuevo producto')
@section('page-subtitle', 'Registro de producto y presentación principal')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Registrar producto</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Complete los datos del producto y su presentación principal para dejarlo listo para vender, comprar o usar en inventario.
        </p>
    </div>

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

    <form method="POST" action="{{ route('productos.store') }}">
        @csrf

        <h3 style="color: #4C1D95; margin-top: 0;">1. Datos generales del producto</h3>

        <div class="form-grid">
            <div class="form-group">
                <label for="nombre_comercial">Nombre comercial</label>
                <input type="text" name="nombre_comercial" id="nombre_comercial" class="form-control"
                    value="{{ old('nombre_comercial') }}" required>
            </div>

            <div class="form-group">
                <label for="nombre_generico">Nombre genérico</label>
                <input type="text" name="nombre_generico" id="nombre_generico" class="form-control"
                    value="{{ old('nombre_generico') }}">
            </div>

            <div class="form-group">
                <label for="concentracion">Concentración</label>
                <input type="text" name="concentracion" id="concentracion" class="form-control"
                    value="{{ old('concentracion') }}" placeholder="Ej. 500mg, 75mg/3ml">
            </div>

            <div class="form-group">
                <label for="tipo_producto">Tipo de producto</label>
                <select name="tipo_producto" id="tipo_producto" class="form-control" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="medicamento" {{ old('tipo_producto') == 'medicamento' ? 'selected' : '' }}>Medicamento</option>
                    <option value="insumo_medico" {{ old('tipo_producto') == 'insumo_medico' ? 'selected' : '' }}>Insumo médico</option>
                    <option value="producto_general" {{ old('tipo_producto') == 'producto_general' ? 'selected' : '' }}>Producto general</option>
                    <option value="higiene" {{ old('tipo_producto') == 'higiene' ? 'selected' : '' }}>Higiene</option>
                    <option value="bebe" {{ old('tipo_producto') == 'bebe' ? 'selected' : '' }}>Bebé</option>
                    <option value="otro" {{ old('tipo_producto') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-control">
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
                <select name="laboratorio_id" id="laboratorio_id" class="form-control">
                    <option value="">Sin laboratorio / marca</option>
                    @foreach ($laboratorios as $laboratorio)
                        <option value="{{ $laboratorio->id }}" {{ old('laboratorio_id') == $laboratorio->id ? 'selected' : '' }}>
                            {{ $laboratorio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="proveedor_id">Proveedor</label>
                <select name="proveedor_id" id="proveedor_id" class="form-control">
                    <option value="">Sin proveedor</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                            {{ $proveedor->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
        </div>

        <hr style="margin: 26px 0; border: none; border-top: 1px solid #E5E7EB;">

        <h3 style="color: #4C1D95; margin-top: 0;">2. Presentación principal para venta</h3>

        <p style="color: #6B7280; margin-top: -6px;">
            Esta es la forma principal en la que se venderá, comprará o usará el producto.
            Ejemplo: unidad, ampolla, blíster x 10, caja x 100.
        </p>

        <div class="form-grid">
            <div class="form-group">
                <label for="presentacion_id">Presentación</label>
                <select name="presentacion_id" id="presentacion_id" class="form-control" required>
                    <option value="">Seleccione una presentación</option>
                    @foreach ($presentaciones as $presentacion)
                        <option value="{{ $presentacion->id }}" {{ old('presentacion_id') == $presentacion->id ? 'selected' : '' }}>
                            {{ $presentacion->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="nombre_mostrado">Nombre mostrado en venta</label>
                <input type="text" name="nombre_mostrado" id="nombre_mostrado" class="form-control"
                    value="{{ old('nombre_mostrado') }}"
                    placeholder="Ej. Diclofenaco 75mg/3ml Bagó - Ampolla"
                    required>
            </div>

            <div class="form-group">
                <label for="unidades_equivalentes">Unidades equivalentes</label>
                <input type="number" name="unidades_equivalentes" id="unidades_equivalentes" class="form-control"
                    value="{{ old('unidades_equivalentes', 1) }}"
                    min="1" required>
                <small style="color: #6B7280;">
                    Ej. unidad = 1, blíster x10 = 10, caja x100 = 100.
                </small>
            </div>

            <div class="form-group">
                <label for="precio_compra">Precio compra</label>
                <input type="number" step="0.01" name="precio_compra" id="precio_compra" class="form-control"
                    value="{{ old('precio_compra') }}"
                    min="0" required>
            </div>

            <div class="form-group">
                <label for="precio_venta">Precio venta</label>
                <input type="number" step="0.01" name="precio_venta" id="precio_venta" class="form-control"
                    value="{{ old('precio_venta') }}"
                    min="0" required>
            </div>

            <div class="form-group">
                <label for="codigo_barras">Código de barras</label>
                <input type="text" name="codigo_barras" id="codigo_barras" class="form-control"
                    value="{{ old('codigo_barras') }}"
                    placeholder="Opcional">
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            @if (auth()->user()->tienePermiso('crear_producto'))
                <button type="submit" class="btn-primary">
                    Guardar producto
                </button>
            @endif

            @if (auth()->user()->tienePermiso('ver_productos'))
                <a href="{{ route('productos.index') }}" class="btn-secondary">
                    Cancelar
                </a>
            @endif
        </div>
    </form>
    @endif
</div>
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