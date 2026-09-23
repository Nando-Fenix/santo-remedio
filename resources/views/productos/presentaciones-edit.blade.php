@extends('layouts.app')

@section('title', 'Editar forma de venta | Santo Remedio')
@section('page-title', 'Editar forma de venta')
@section('page-subtitle', 'Modificar presentación, precio y código de barras')

@section('content')

@if (!auth()->user()->tienePermiso('editar_producto'))
    <div class="alert-danger">
        No tiene permiso para editar formas de venta.
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

<form
    method="POST"
    action="{{ route('productos.presentaciones.update', [$producto, $productoPresentacion]) }}"
    id="form_editar_presentacion"
>
    @csrf
    @method('PUT')

    <div class="product-presentation-edit-layout">

        <section class="product-presentation-edit-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-layers"></i>
                            {{ $producto->nombre_comercial }}
                        </h2>

                        <p>
                            Modifique la forma en que este producto se vende, compra o utiliza.
                        </p>
                    </div>

                    <a href="{{ route('productos.presentaciones.index', $producto) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="product-presentation-edit-info">
                    <div>
                        <span>Presentación actual</span>
                        <strong>{{ $productoPresentacion->presentacion->nombre ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Nombre visible</span>
                        <strong>{{ $productoPresentacion->nombre_mostrado }}</strong>
                    </div>

                    <div>
                        <span>Unidades</span>
                        <strong>{{ $productoPresentacion->unidades_equivalentes }}</strong>
                    </div>

                    <div>
                        <span>Estado</span>
                        <strong>{{ ucfirst($productoPresentacion->estado) }}</strong>
                    </div>
                </div>
            </div>

            <div class="product-presentation-edit-help">
                <div>
                    <i class="bi bi-info-circle"></i>
                </div>

                <section>
                    <h3>Antes de guardar</h3>

                    <ul>
                        <li>Verifique que las unidades equivalentes sean correctas.</li>
                        <li>El precio de venta será usado en el módulo de ventas.</li>
                        <li>El código de barras debe ser único para evitar confusiones.</li>
                        <li>Solo una forma de venta debe quedar como principal.</li>
                    </ul>
                </section>
            </div>

        </section>

        <aside class="product-presentation-edit-side">

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-pencil-square"></i>
                        Datos de la presentación
                    </h3>
                </div>

                <div class="form-group">
                    <label for="presentacion_id">Forma de venta *</label>
                    <select name="presentacion_id" id="presentacion_id" required>
                        <option value="">Seleccione</option>

                        @foreach ($presentaciones as $presentacion)
                            <option
                                value="{{ $presentacion->id }}"
                                @selected(old('presentacion_id', $productoPresentacion->presentacion_id) == $presentacion->id)
                            >
                                {{ $presentacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="nombre_mostrado">Nombre que verá el vendedor *</label>
                    <input
                        type="text"
                        name="nombre_mostrado"
                        id="nombre_mostrado"
                        value="{{ old('nombre_mostrado', $productoPresentacion->nombre_mostrado) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="unidades_equivalentes">¿Cuántas unidades descuenta? *</label>
                    <input
                        type="number"
                        name="unidades_equivalentes"
                        id="unidades_equivalentes"
                        min="1"
                        value="{{ old('unidades_equivalentes', $productoPresentacion->unidades_equivalentes) }}"
                        required
                    >

                    <small class="form-help">
                        Unidad = 1, blíster x 10 = 10, caja x 100 = 100.
                    </small>
                </div>

                <div class="product-presentation-edit-price-grid">
                    <div class="form-group">
                        <label for="precio_compra">Precio compra *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="precio_compra"
                            id="precio_compra"
                            value="{{ old('precio_compra', $productoPresentacion->precio_compra) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="precio_venta">Precio venta *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="precio_venta"
                            id="precio_venta"
                            value="{{ old('precio_venta', $productoPresentacion->precio_venta) }}"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="codigo_barras">Código de barras</label>

                    <div class="barcode-input-group">
                        <input
                            type="text"
                            id="codigo_barras"
                            name="codigo_barras"
                            value="{{ old('codigo_barras', $productoPresentacion->codigosBarras->where('estado', 'activo')->first()->codigo ?? '') }}"
                            placeholder="Escanee o escriba el código"
                        >

                        <button type="button" class="btn-secondary" onclick="activarEscaner()">
                            <i class="bi bi-upc-scan"></i>
                        </button>
                    </div>
                </div>

                <label class="checkbox-line product-presentation-main-check">
                    <input
                        type="checkbox"
                        name="es_principal"
                        value="1"
                        @checked(old('es_principal', $productoPresentacion->es_principal))
                    >
                    Usar como forma principal de venta
                </label>

                <small class="form-help">
                    Esta será la opción que se mostrará como principal en el listado de productos.
                </small>

                <div class="product-presentation-edit-actions">
                    <a href="{{ route('productos.presentaciones.index', $producto) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Guardar
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
const formEditarPresentacion = document.getElementById('form_editar_presentacion');
const unidadesEquivalentes = document.getElementById('unidades_equivalentes');
const precioCompra = document.getElementById('precio_compra');
const precioVenta = document.getElementById('precio_venta');
const nombreMostrado = document.getElementById('nombre_mostrado');

formEditarPresentacion.addEventListener('submit', function (event) {
    event.preventDefault();

    const unidades = Number(unidadesEquivalentes.value || 0);
    const compra = Number(precioCompra.value || 0);
    const venta = Number(precioVenta.value || 0);
    const nombre = nombreMostrado.value.trim();

    if (nombre.length < 3) {
        Swal.fire({
            icon: 'warning',
            title: 'Nombre requerido',
            text: 'El nombre visible debe tener al menos 3 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (unidades < 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Unidades inválidas',
            text: 'Las unidades equivalentes deben ser al menos 1.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (compra < 0 || venta < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Precio inválido',
            text: 'Los precios no pueden ser negativos.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    Swal.fire({
        icon: 'question',
        title: '¿Guardar cambios?',
        text: 'Se actualizará la forma de venta del producto.',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});

function activarEscaner() {
    const input = document.getElementById('codigo_barras');
    input.focus();
    input.select();
}
</script>

@endif

@endsection