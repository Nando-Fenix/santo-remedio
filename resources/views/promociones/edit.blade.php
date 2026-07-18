@extends('layouts.app')

@section('title', 'Editar promoción | Santo Remedio')
@section('page-title', 'Editar promoción')
@section('page-subtitle', 'Modificar oferta, combo o promoción por vencimiento')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Editar promoción</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Puede modificar los datos generales y los productos incluidos en la promoción.
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

    <form method="POST" action="{{ route('promociones.update', $promocion) }}" id="form_promocion">
        @csrf
        @method('PUT')

        <div class="card" style="background: #FAFAFA; margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Datos de la promoción</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $promocion->nombre) }}" required>
                </div>

                <div class="form-group">
                    <label>Tipo *</label>
                    <select name="tipo" id="tipo_promocion" required>
                        <option value="producto_individual" @selected(old('tipo', $promocion->tipo) === 'producto_individual')>Producto individual</option>
                        <option value="combo" @selected(old('tipo', $promocion->tipo) === 'combo')>Combo</option>
                        <option value="por_vencimiento" @selected(old('tipo', $promocion->tipo) === 'por_vencimiento')>Por vencimiento</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Precio promocional *</label>
                    <input type="number" step="0.01" min="0" name="precio_promocional" value="{{ old('precio_promocional', $promocion->precio_promocional) }}" required>
                </div>

                <div class="form-group">
                    <label>Sucursal</label>
                    <select name="sucursal_id">
                        <option value="">Todas las sucursales</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" @selected(old('sucursal_id', $promocion->sucursal_id) == $sucursal->id)>
                                {{ $sucursal->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $promocion->fecha_inicio?->format('Y-m-d')) }}">
                </div>

                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="fecha_fin" value="{{ old('fecha_fin', $promocion->fecha_fin?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px;">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3">{{ old('descripcion', $promocion->descripcion) }}</textarea>
            </div>

            <div class="form-group">
                <label>Motivo</label>
                <input type="text" name="motivo" value="{{ old('motivo', $promocion->motivo) }}">
            </div>
        </div>

        <div class="card" style="margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Productos incluidos</h3>

            <div class="form-group">
                <label>Buscar producto o escanear código</label>
                <input
                    type="text"
                    id="buscador_producto_promocion"
                    placeholder="Buscar por producto, forma de venta, laboratorio o código"
                    autocomplete="off"
                >
            </div>

            <div id="resultados_producto_promocion" class="card" style="display:none; margin-top:12px; background:#FAFAFA;"></div>

            <div class="table-container" style="margin-top: 18px;">
                <table class="table" id="tabla_items_promocion">
                    <thead>
                        <tr>
                            <th>Producto incluido</th>
                            <th>Forma</th>
                            <th>Stock</th>
                            <th>Cantidad</th>
                            <th>Unidades necesarias</th>
                            <th>Precio referencia</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="promocion_vacia">
                            <td colspan="7" style="text-align:center; color:#6B7280;">
                                No hay productos agregados a la promoción.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="inputs_items_promocion"></div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="submit" class="btn-primary">
                Guardar cambios
            </button>

            <a href="{{ route('promociones.show', $promocion) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

@php
    $itemsInicialesPromocion = $promocion->items->map(function ($item) {
        return [
            'producto_id' => $item->producto_id,
            'producto_presentacion_id' => $item->producto_presentacion_id,
            'nombre_mostrado' => $item->productoPresentacion->nombre_mostrado ?? $item->producto->nombre_comercial ?? '-',
            'laboratorio' => $item->producto->laboratorio->nombre ?? '',
            'concentracion' => $item->producto->concentracion ?? '',
            'presentacion' => $item->productoPresentacion->presentacion->nombre ?? '',
            'unidades_equivalentes' => max((int) ($item->productoPresentacion->unidades_equivalentes ?? 1), 1),
            'stock_disponible' => 0,
            'cantidad' => (int) $item->cantidad,
            'precio_referencia' => (float) $item->precio_referencia,
            'lote_id' => $item->lote_id ?? '',
        ];
    })->values();
@endphp

<script>
const buscarPromocionProductosUrl = "{{ route('promociones.buscar-productos') }}";

const buscadorPromocion = document.getElementById('buscador_producto_promocion');
const resultadosPromocion = document.getElementById('resultados_producto_promocion');
const tablaItemsPromocion = document.querySelector('#tabla_items_promocion tbody');
const promocionVacia = document.getElementById('promocion_vacia');
const inputsItemsPromocion = document.getElementById('inputs_items_promocion');

let itemsPromocion = @json($itemsInicialesPromocion);

let timeoutPromocion = null;

buscadorPromocion.addEventListener('input', function () {
    clearTimeout(timeoutPromocion);

    const termino = this.value.trim();

    if (termino.length < 2) {
        resultadosPromocion.style.display = 'none';
        resultadosPromocion.innerHTML = '';
        return;
    }

    timeoutPromocion = setTimeout(() => {
        buscarProductosPromocion(termino);
    }, 250);
});

async function buscarProductosPromocion(termino) {
    try {
        const response = await fetch(`${buscarPromocionProductosUrl}?busqueda=${encodeURIComponent(termino)}`);
        const data = await response.json();

        mostrarResultadosPromocion(data);
    } catch (error) {
        resultadosPromocion.style.display = 'block';
        resultadosPromocion.innerHTML = '<p style="color:#991B1B;">Error al buscar productos.</p>';
    }
}

function mostrarResultadosPromocion(productos) {
    resultadosPromocion.style.display = 'block';

    if (!Array.isArray(productos) || productos.length === 0) {
        resultadosPromocion.innerHTML = '<p style="color:#6B7280;">No se encontraron productos.</p>';
        return;
    }

    resultadosPromocion.innerHTML = productos.map(producto => {
        const dataProducto = encodeURIComponent(JSON.stringify(producto));

        const nombreVisible = producto.nombre_mostrado || producto.nombre;
        const laboratorio = producto.laboratorio ? ` | ${producto.laboratorio}` : '';
        const tipo = producto.tipo_producto ? producto.tipo_producto.replace('_', ' ') : '';
        const unidades = Number(producto.unidades_equivalentes || 1);

        return `
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #E5E7EB;">
                <div>
                    <strong>${nombreVisible}</strong>

                    <div style="color:#6B7280; font-size:13px; margin-top:3px;">
                        ${tipo}${laboratorio}
                    </div>

                    <div style="color:#4C1D95; font-size:13px; margin-top:3px;">
                        Precio normal: ${Number(producto.precio_venta).toFixed(2)} Bs —
                        Stock: ${producto.stock_disponible}
                    </div>

                    <div style="color:#6B7280; font-size:13px; margin-top:3px;">
                        Cada cantidad descuenta ${unidades} unidad(es).
                    </div>
                </div>

                <button type="button" class="btn-primary" data-producto="${dataProducto}" onclick="agregarItemDesdeBoton(this)">
                    Agregar
                </button>
            </div>
        `;
    }).join('');
}

function agregarItemDesdeBoton(boton) {
    try {
        const producto = JSON.parse(decodeURIComponent(boton.dataset.producto));
        agregarItemPromocion(producto);
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo seleccionar el producto.',
            confirmButtonColor: '#6D28D9'
        });
    }
}

function agregarItemPromocion(producto) {
    const existe = itemsPromocion.find(item => item.producto_presentacion_id === Number(producto.id));

    if (existe) {
        existe.cantidad += 1;
    } else {
        itemsPromocion.push({
            producto_id: Number(producto.producto_id),
            producto_presentacion_id: Number(producto.id),
            nombre_mostrado: producto.nombre_mostrado || producto.nombre,
            laboratorio: producto.laboratorio || '',
            concentracion: producto.concentracion || '',
            presentacion: producto.presentacion || '',
            unidades_equivalentes: Number(producto.unidades_equivalentes || 1),
            stock_disponible: Number(producto.stock_disponible || 0),
            cantidad: 1,
            precio_referencia: Number(producto.precio_venta || 0),
            lote_id: ''
        });
    }

    buscadorPromocion.value = '';
    resultadosPromocion.style.display = 'none';
    resultadosPromocion.innerHTML = '';

    renderItemsPromocion();
}

function cambiarCantidadPromocion(index, cantidad) {
    cantidad = parseInt(cantidad || 1);

    if (cantidad < 1) {
        cantidad = 1;
    }

    itemsPromocion[index].cantidad = cantidad;
    renderItemsPromocion();
}

function quitarItemPromocion(index) {
    itemsPromocion.splice(index, 1);
    renderItemsPromocion();
}

function renderItemsPromocion() {
    tablaItemsPromocion.innerHTML = '';
    inputsItemsPromocion.innerHTML = '';

    if (itemsPromocion.length === 0) {
        tablaItemsPromocion.appendChild(promocionVacia);
        promocionVacia.style.display = '';
        return;
    }

    promocionVacia.style.display = 'none';

    itemsPromocion.forEach((item, index) => {
        const unidadesNecesarias = item.cantidad * item.unidades_equivalentes;

        tablaItemsPromocion.innerHTML += `
            <tr>
                <td>
                    <strong>${item.nombre_mostrado}</strong>
                    <br>
                    <small style="color:#6B7280;">
                        ${item.laboratorio ? item.laboratorio + ' | ' : ''}
                        ${item.concentracion || ''}
                    </small>
                </td>

                <td>${item.presentacion || '-'}</td>
                <td>${item.stock_disponible}</td>

                <td>
                    <input
                        type="number"
                        min="1"
                        value="${item.cantidad}"
                        style="width:80px;"
                        onchange="cambiarCantidadPromocion(${index}, this.value)"
                    >
                </td>

                <td>
                    ${unidadesNecesarias}
                    <br>
                    <small style="color:#6B7280;">
                        ${item.cantidad} x ${item.unidades_equivalentes}
                    </small>
                </td>

                <td>${Number(item.precio_referencia).toFixed(2)} Bs</td>

                <td>
                    <button type="button" class="btn-danger" onclick="quitarItemPromocion(${index})">
                        Quitar
                    </button>
                </td>
            </tr>
        `;

        inputsItemsPromocion.innerHTML += `
            <input type="hidden" name="items[${index}][producto_id]" value="${item.producto_id}">
            <input type="hidden" name="items[${index}][producto_presentacion_id]" value="${item.producto_presentacion_id}">
            <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
            <input type="hidden" name="items[${index}][unidades_necesarias]" value="${unidadesNecesarias}">
            <input type="hidden" name="items[${index}][precio_referencia]" value="${item.precio_referencia}">
            <input type="hidden" name="items[${index}][lote_id]" value="${item.lote_id}">
        `;
    });
}

document.getElementById('form_promocion').addEventListener('submit', function (event) {
    if (itemsPromocion.length === 0) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Promoción vacía',
            text: 'Debe agregar al menos un producto a la promoción.',
            confirmButtonColor: '#6D28D9'
        });
    }
});

renderItemsPromocion();
</script>

@endsection