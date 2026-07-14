@extends('layouts.app')

@section('title', 'Editar forma de venta | Santo Remedio')
@section('page-title', 'Editar forma de venta')
@section('page-subtitle', 'Modificar presentación, precio y código de barras')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">
            {{ $producto->nombre_comercial }}
        </h2>

        <p style="margin: 6px 0 0; color: #6B7280;">
            Modifique la forma en que este producto se vende, compra o utiliza.
        </p>
    </div>

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

        <form method="POST" action="{{ route('productos.presentaciones.update', [$producto, $productoPresentacion]) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="presentacion_id">Forma de venta *</label>
                    <select name="presentacion_id" id="presentacion_id" class="form-control" required>
                        <option value="">Seleccione</option>
                        @foreach ($presentaciones as $presentacion)
                            <option value="{{ $presentacion->id }}"
                                @selected(old('presentacion_id', $productoPresentacion->presentacion_id) == $presentacion->id)>
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
                        class="form-control"
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
                        class="form-control"
                        min="1"
                        value="{{ old('unidades_equivalentes', $productoPresentacion->unidades_equivalentes) }}"
                        required
                    >
                    <small style="color: #6B7280;">
                        Unidad = 1, blíster x 10 = 10, caja x 100 = 100.
                    </small>
                </div>

                <div class="form-group">
                    <label for="precio_compra">Precio compra *</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="precio_compra"
                        id="precio_compra"
                        class="form-control"
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
                        class="form-control"
                        value="{{ old('precio_venta', $productoPresentacion->precio_venta) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="codigo_barras">Código de barras</label>
                    <input
                        type="text"
                        id="codigo_barras"
                        name="codigo_barras"
                        class="form-control"
                        value="{{ old('codigo_barras', $productoPresentacion->codigosBarras->where('estado', 'activo')->first()->codigo ?? '') }}"
                        placeholder="Escanee o escriba el código"
                    >
                </div>

                <div class="form-group" style="display: flex; align-items: end;">
                    <button type="button" class="btn-secondary" onclick="activarEscaner()">
                        Enfocar lector
                    </button>
                </div>
            </div>

            <label class="checkbox-line" style="margin-top: 18px;">
                <input type="checkbox" name="es_principal" value="1"
                    @checked(old('es_principal', $productoPresentacion->es_principal))>
                Usar como forma principal de venta
            </label>

            <small style="display: block; color: #6B7280; margin-top: 4px;">
                Esta será la opción que se mostrará como principal en el listado de productos.
            </small>

            <div style="display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap;">
                <button type="submit" class="btn-primary">
                    Guardar cambios
                </button>

                <a href="{{ route('productos.presentaciones.index', $producto) }}" class="btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    @endif
</div>

<script>
function activarEscaner() {
    const input = document.getElementById('codigo_barras');
    input.focus();
    input.select();
}
</script>

@endsection