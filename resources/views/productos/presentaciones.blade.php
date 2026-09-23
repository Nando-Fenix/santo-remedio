@extends('layouts.app')

@section('title', 'Presentaciones | Santo Remedio')
@section('page-title', 'Formas de venta del producto')
@section('page-subtitle', 'Presentaciones, precios, equivalencias y códigos de barras')

@section('content')

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert-danger">
        {{ session('error') }}
    </div>
@endif

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

<div class="product-presentations-layout">

    <section class="product-presentations-main">

        <div class="compact-card">
            <div class="compact-header">
                <div>
                    <h2>
                        <i class="bi bi-layers"></i>
                        {{ $producto->nombre_comercial }}
                    </h2>

                    <p>
                        {{ $producto->nombre_generico ?? 'Sin nombre genérico' }}
                        @if($producto->concentracion)
                            · {{ $producto->concentracion }}
                        @endif
                    </p>
                </div>

                @if (auth()->user()->tienePermiso('ver_productos'))
                    <a href="{{ route('productos.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                @endif
            </div>

            <div class="product-presentations-info">
                <div>
                    <span>Categoría</span>
                    <strong>{{ $producto->categoria->nombre ?? '-' }}</strong>
                </div>

                <div>
                    <span>Laboratorio / Marca</span>
                    <strong>{{ $producto->laboratorio->nombre ?? '-' }}</strong>
                </div>

                <div>
                    <span>Tipo</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $producto->tipo_producto ?? '-')) }}</strong>
                </div>

                <div>
                    <span>Estado</span>
                    <strong>{{ ucfirst($producto->estado) }}</strong>
                </div>
            </div>
        </div>

        <div class="detail-section-card compact-section">
            <div class="detail-section-head">
                <h3>
                    <i class="bi bi-list-check"></i>
                    Formas de venta registradas
                </h3>
            </div>

            <p class="product-presentations-help">
                Estas son las opciones disponibles para vender, comprar o usar este producto.
            </p>

            <div class="table-container compact-table-container">
                <table class="table compact-table product-presentations-table">
                    <thead>
                        <tr>
                            <th>Forma</th>
                            <th>Nombre visible</th>
                            <th>Descuenta</th>
                            <th>P. compra</th>
                            <th>P. venta</th>
                            <th>Códigos</th>
                            <th>Principal</th>
                            <th>Estado</th>
                            <th class="table-actions-cell">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($producto->presentaciones as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->presentacion->nombre ?? '-' }}</strong>
                                </td>

                                <td>
                                    {{ $item->nombre_mostrado }}
                                </td>

                                <td>
                                    <span class="badge badge-soft">
                                        {{ $item->unidades_equivalentes }} unidad(es)
                                    </span>
                                </td>

                                <td>
                                    {{ number_format($item->precio_compra, 2) }} Bs
                                </td>

                                <td>
                                    <strong>{{ number_format($item->precio_venta, 2) }} Bs</strong>
                                </td>

                                <td>
                                    <div class="presentation-codes">
                                        @forelse ($item->codigosBarras as $codigo)
                                            <span class="badge badge-soft">
                                                {{ $codigo->codigo }}
                                            </span>
                                        @empty
                                            -
                                        @endforelse
                                    </div>
                                </td>

                                <td>
                                    @if ($item->es_principal)
                                        <span class="badge badge-success">Sí</span>
                                    @else
                                        <span class="badge badge-soft">No</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($item->estado === 'activo')
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="action-group">
                                        @if ($item->estado === 'activo' && auth()->user()->tienePermiso('editar_producto'))
                                            <a
                                                href="{{ route('productos.presentaciones.edit', [$producto, $item]) }}"
                                                class="icon-action icon-action-edit"
                                                title="Editar presentación"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif

                                        @if ($item->estado === 'activo' && auth()->user()->tienePermiso('desactivar_producto'))
                                            <form
                                                method="POST"
                                                action="{{ route('productos.presentaciones.destroy', [$producto, $item]) }}"
                                                class="form-desactivar-presentacion"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    class="icon-action icon-action-danger"
                                                    type="submit"
                                                    title="Desactivar presentación"
                                                >
                                                    <i class="bi bi-power"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($item->estado !== 'activo')
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-table-message">
                                    Este producto todavía no tiene presentaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>

    @if (auth()->user()->tienePermiso('editar_producto'))
        <aside class="product-presentations-side">

            <form
                method="POST"
                action="{{ route('productos.presentaciones.store', $producto) }}"
                class="cancel-form-card"
            >
                @csrf

                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-plus-circle"></i>
                        Agregar forma de venta
                    </h3>
                </div>

                <p class="product-presentations-help">
                    Use esta sección si el producto se vende, compra o utiliza en más de una forma.
                </p>

                <div class="form-group">
                    <label>Forma de venta *</label>
                    <select name="presentacion_id" id="presentacion_id">
                        <option value="">Seleccione</option>
                        @foreach ($presentaciones as $presentacion)
                            <option value="{{ $presentacion->id }}" @selected(old('presentacion_id') == $presentacion->id)>
                                {{ $presentacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Nombre que verá el vendedor *</label>
                    <input
                        type="text"
                        name="nombre_mostrado"
                        id="nombre_mostrado"
                        value="{{ old('nombre_mostrado') }}"
                        placeholder="Ej: Paracetamol 500mg - Caja x 100"
                    >
                </div>

                <div class="form-group">
                    <label>¿Cuántas unidades descuenta? *</label>
                    <input
                        type="number"
                        min="1"
                        name="unidades_equivalentes"
                        value="{{ old('unidades_equivalentes', 1) }}"
                    >
                    <small class="form-help">
                        Unidad = 1, blíster x 10 = 10, caja x 100 = 100.
                    </small>
                </div>

                <div class="product-presentations-price-grid">
                    <div class="form-group">
                        <label>Precio compra *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="precio_compra"
                            value="{{ old('precio_compra', 0) }}"
                        >
                    </div>

                    <div class="form-group">
                        <label>Precio venta *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="precio_venta"
                            value="{{ old('precio_venta', 0) }}"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label>Código de barras</label>

                    <div class="barcode-input-group">
                        <input
                            type="text"
                            id="codigo_barras"
                            name="codigo_barras"
                            value="{{ old('codigo_barras') }}"
                            placeholder="Escanee el código"
                        >

                        <button type="button" class="btn-secondary" onclick="activarEscaner()">
                            <i class="bi bi-upc-scan"></i>
                        </button>
                    </div>
                </div>

                <label class="checkbox-line product-main-check">
                    <input type="checkbox" name="es_principal" value="1" @checked(old('es_principal'))>
                    Usar como forma principal de venta
                </label>

                <small class="form-help">
                    Esta será la opción que se mostrará como principal en el listado de productos.
                </small>

                <div class="product-presentations-actions">
                    <button class="btn-primary" type="submit">
                        <i class="bi bi-check2-circle"></i>
                        Guardar
                    </button>
                </div>
            </form>

        </aside>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const presentacionSelect = document.getElementById('presentacion_id');
    const nombreMostrado = document.getElementById('nombre_mostrado');

    const productoBase = @json(
        trim(
            $producto->nombre_comercial . ' ' .
            ($producto->concentracion ?? '') . ' ' .
            ($producto->laboratorio->nombre ?? '')
        )
    );

    function obtenerTextoSelect(select) {
        if (!select || !select.value) {
            return '';
        }

        return select.options[select.selectedIndex].text.trim();
    }

    function generarNombreMostrado() {
        if (!nombreMostrado || nombreMostrado.value.trim() !== '') {
            return;
        }

        const presentacionTexto = obtenerTextoSelect(presentacionSelect);

        if (!presentacionTexto || presentacionTexto === 'Seleccione') {
            return;
        }

        nombreMostrado.value = productoBase + ' - ' + presentacionTexto;
    }

    if (presentacionSelect) {
        presentacionSelect.addEventListener('change', generarNombreMostrado);
    }

    document.querySelectorAll('.form-desactivar-presentacion').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: '¿Desactivar forma de venta?',
                text: 'Esta presentación quedará inactiva para nuevas ventas.',
                showCancelButton: true,
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#6B7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit();
                }
            });
        });
    });
});

function activarEscaner() {
    const input = document.getElementById('codigo_barras');
    input.focus();
    input.select();
}
</script>

@endsection