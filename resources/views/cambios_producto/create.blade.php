@extends('layouts.app')

@section('title', 'Cambio de producto | Santo Remedio')
@section('page-title', 'Cambio de producto')
@section('page-subtitle', 'Devolver un producto vendido y entregar otro producto')

@section('content')

@if (!auth()->user()->tienePermiso('cambiar_producto'))
    <div class="alert-danger">
        No tiene permiso para registrar cambios de producto.
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

<form method="POST" action="{{ route('cambios-producto.store', $venta) }}" id="form_cambio_producto">
    @csrf

    <div class="exchange-layout">

        <section class="exchange-main">

            <div class="compact-card">
                <div class="compact-header">
                    <div>
                        <h2>
                            <i class="bi bi-arrow-left-right"></i>
                            Cambio de producto - Venta {{ $venta->numero_venta }}
                        </h2>

                        <p>
                            Seleccione el producto devuelto y el producto nuevo que se entregará.
                        </p>
                    </div>

                    <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>

                <div class="detail-stat-grid compact-detail-stats">
                    <div class="detail-stat-card detail-stat-main">
                        <span>Total venta</span>
                        <strong>{{ number_format($venta->total, 2) }} Bs</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Cliente</span>
                        <strong>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Sucursal</span>
                        <strong>{{ $venta->sucursal->nombre ?? '-' }}</strong>
                    </div>

                    <div class="detail-stat-card">
                        <span>Vendedor</span>
                        <strong>{{ $venta->usuario->nombre ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="detail-section-card compact-section">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-arrow-return-left"></i>
                        1. Producto que el cliente devuelve
                    </h3>
                </div>

                <div class="table-container compact-table-container">
                    <table class="table compact-table exchange-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Producto vendido</th>
                                <th>Presentación</th>
                                <th>Vendida</th>
                                <th>Usada</th>
                                <th>Disponible</th>
                                <th>P. Unit.</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($venta->detalles as $detalle)
                                @php
                                    $cantidadYaReembolsada = $detalle->reembolsos()
                                        ->whereHas('reembolso', function ($query) {
                                            $query->where('estado', 'registrado');
                                        })
                                        ->sum('cantidad_devuelta');

                                    $cantidadYaCambiada = $detalle->cambiosProductoDevueltos()
                                        ->whereHas('cambioProducto', function ($query) {
                                            $query->where('estado', 'registrado');
                                        })
                                        ->sum('cantidad_devuelta');

                                    $cantidadUsada = $cantidadYaReembolsada + $cantidadYaCambiada;
                                    $cantidadDisponible = $detalle->cantidad - $cantidadUsada;
                                @endphp

                                <tr>
                                    <td>
                                        <input
                                            type="radio"
                                            name="detalle_venta_devuelto_id"
                                            value="{{ $detalle->id }}"
                                            data-precio="{{ $detalle->precio_unitario }}"
                                            data-disponible="{{ $cantidadDisponible }}"
                                            {{ $cantidadDisponible <= 0 ? 'disabled' : '' }}
                                            required
                                        >
                                    </td>

                                    <td>
                                        <strong>{{ $detalle->producto->nombre_comercial ?? '-' }}</strong>

                                        @if ($detalle->producto?->concentracion)
                                            <br>
                                            <small style="color:#6B7280;">
                                                {{ $detalle->producto->concentracion }}
                                            </small>
                                        @endif

                                        <div class="mini-lot-list" style="margin-top:5px;">
                                            @forelse ($detalle->lotesDescontados as $loteDescontado)
                                                <div class="mini-lot-line">
                                                    <strong>{{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}</strong>
                                                    <small>
                                                        {{ $loteDescontado->unidades_descontadas }} unidad(es)
                                                    </small>
                                                </div>
                                            @empty
                                                <small style="color:#6B7280;">Sin lote</small>
                                            @endforelse
                                        </div>
                                    </td>

                                    <td>
                                        {{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}

                                        @if ($detalle->productoPresentacion?->unidades_equivalentes)
                                            <br>
                                            <small style="color:#6B7280;">
                                                {{ $detalle->productoPresentacion->unidades_equivalentes }} unidad(es)
                                            </small>
                                        @endif
                                    </td>

                                    <td><strong>{{ $detalle->cantidad }}</strong></td>
                                    <td>{{ $cantidadUsada }}</td>

                                    <td>
                                        @if ($cantidadDisponible > 0)
                                            <span class="badge badge-success">{{ $cantidadDisponible }}</span>
                                        @else
                                            <span class="badge badge-danger">0</span>
                                        @endif
                                    </td>

                                    <td>{{ number_format($detalle->precio_unitario, 2) }} Bs</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="exchange-quantity-row">
                    <div class="form-group">
                        <label>Cantidad devuelta *</label>
                        <input
                            type="number"
                            name="cantidad_devuelta"
                            id="cantidad_devuelta"
                            min="1"
                            value="{{ old('cantidad_devuelta', 1) }}"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="detail-section-card compact-section">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-box-seam"></i>
                        2. Producto nuevo que se entregará
                    </h3>
                </div>

                <div class="exchange-new-product-grid">
                    <div class="form-group">
                        <label>Buscar producto nuevo *</label>

                        <div class="promo-search-box">
                            <i class="bi bi-search"></i>
                            <input
                                type="text"
                                id="buscador_producto_nuevo"
                                placeholder="Nombre, concentración o código de barras"
                                autocomplete="off"
                            >
                        </div>

                        <div id="resultados_producto_nuevo" class="exchange-results"></div>
                    </div>

                    <div class="form-group">
                        <label>Cantidad nueva *</label>
                        <input
                            type="number"
                            name="cantidad_nueva"
                            id="cantidad_nueva"
                            min="1"
                            value="{{ old('cantidad_nueva', 1) }}"
                            required
                        >
                    </div>
                </div>

                <input
                    type="hidden"
                    name="producto_presentacion_nueva_id"
                    id="producto_presentacion_nueva_id"
                    value="{{ old('producto_presentacion_nueva_id') }}"
                >

                <div id="producto_nuevo_seleccionado" class="exchange-selected-product" style="display:none;">
                    <i class="bi bi-check-circle"></i>
                    <div>
                        <span>Producto nuevo seleccionado</span>
                        <strong id="nombre_producto_nuevo"></strong>
                    </div>
                </div>
            </div>

        </section>

        <aside class="exchange-side">

            <div class="cancel-warning-card">
                <div class="cancel-warning-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div>
                    <h3>Acción delicada</h3>
                    <p>
                        Se devolverá stock del producto anterior, se descontará stock del producto nuevo y se ajustará la caja si existe diferencia.
                    </p>
                </div>
            </div>

            <div class="cancel-form-card">
                <div class="detail-section-head">
                    <h3>
                        <i class="bi bi-calculator"></i>
                        Diferencia del cambio
                    </h3>
                </div>

                <div class="exchange-preview">
                    <div>
                        <span>Monto devuelto</span>
                        <strong id="monto_devuelto_preview">0.00 Bs</strong>
                    </div>

                    <div>
                        <span>Monto producto nuevo</span>
                        <strong id="monto_nuevo_preview">0.00 Bs</strong>
                    </div>

                    <div class="exchange-preview-main">
                        <span>Diferencia</span>
                        <strong id="diferencia_preview">0.00 Bs</strong>
                        <small id="tipo_diferencia_preview"></small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Motivo del cambio *</label>
                    <textarea
                        name="motivo"
                        rows="7"
                        required
                        placeholder="Ej: Medicamento equivocado, presentación incorrecta, autorización de farmacia..."
                    >{{ old('motivo') }}</textarea>

                    <small class="cancel-help-text">
                        El motivo debe ser claro para mantener trazabilidad.
                    </small>
                </div>

                <div class="exchange-actions">
                    <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-danger">
                        <i class="bi bi-arrow-left-right"></i>
                        Registrar
                    </button>
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
const formCambioProducto = document.getElementById('form_cambio_producto');

const inputBuscador = document.getElementById('buscador_producto_nuevo');
const contenedorResultados = document.getElementById('resultados_producto_nuevo');
const inputPresentacionNueva = document.getElementById('producto_presentacion_nueva_id');
const boxProductoSeleccionado = document.getElementById('producto_nuevo_seleccionado');
const textoProductoNuevo = document.getElementById('nombre_producto_nuevo');

const inputCantidadDevuelta = document.getElementById('cantidad_devuelta');
const inputCantidadNueva = document.getElementById('cantidad_nueva');

const montoDevueltoPreview = document.getElementById('monto_devuelto_preview');
const montoNuevoPreview = document.getElementById('monto_nuevo_preview');
const diferenciaPreview = document.getElementById('diferencia_preview');
const tipoDiferenciaPreview = document.getElementById('tipo_diferencia_preview');

let precioDevuelto = 0;
let precioNuevo = 0;
let stockProductoNuevo = 0;

function obtenerDetalleSeleccionado() {
    return document.querySelector('input[name="detalle_venta_devuelto_id"]:checked');
}

function calcularVistaPrevia() {
    const detalleSeleccionado = obtenerDetalleSeleccionado();

    if (detalleSeleccionado) {
        precioDevuelto = Number(detalleSeleccionado.dataset.precio || 0);
        const disponible = Number(detalleSeleccionado.dataset.disponible || 0);

        inputCantidadDevuelta.max = disponible;

        if (Number(inputCantidadDevuelta.value) > disponible) {
            inputCantidadDevuelta.value = disponible;
        }
    }

    if (stockProductoNuevo > 0) {
        inputCantidadNueva.max = stockProductoNuevo;

        if (Number(inputCantidadNueva.value) > stockProductoNuevo) {
            inputCantidadNueva.value = stockProductoNuevo;
        }
    }

    const cantidadDevuelta = Number(inputCantidadDevuelta.value || 0);
    const cantidadNueva = Number(inputCantidadNueva.value || 0);

    const montoDevuelto = cantidadDevuelta * precioDevuelto;
    const montoNuevo = cantidadNueva * precioNuevo;
    const diferencia = Math.abs(montoNuevo - montoDevuelto);

    montoDevueltoPreview.textContent = montoDevuelto.toFixed(2) + ' Bs';
    montoNuevoPreview.textContent = montoNuevo.toFixed(2) + ' Bs';
    diferenciaPreview.textContent = diferencia.toFixed(2) + ' Bs';

    if (montoNuevo > montoDevuelto) {
        tipoDiferenciaPreview.textContent = 'El cliente debe pagar la diferencia.';
    } else if (montoNuevo < montoDevuelto) {
        tipoDiferenciaPreview.textContent = 'La farmacia debe devolver la diferencia.';
    } else {
        tipoDiferenciaPreview.textContent = 'No existe diferencia de monto.';
    }
}

document.querySelectorAll('input[name="detalle_venta_devuelto_id"]').forEach(radio => {
    radio.addEventListener('change', calcularVistaPrevia);
});

inputCantidadDevuelta.addEventListener('input', calcularVistaPrevia);
inputCantidadNueva.addEventListener('input', calcularVistaPrevia);

inputBuscador.addEventListener('input', async function () {
    const termino = this.value.trim();

    inputPresentacionNueva.value = '';
    boxProductoSeleccionado.style.display = 'none';
    contenedorResultados.innerHTML = '';

    if (termino.length < 2) {
        return;
    }

    const url = `{{ route('cambios-producto.buscar-productos') }}?busqueda=${encodeURIComponent(termino)}`;
    const respuesta = await fetch(url);
    const productos = await respuesta.json();

    if (productos.length === 0) {
        contenedorResultados.innerHTML = `
            <div class="alert-danger">
                No se encontraron productos con stock disponible.
            </div>
        `;
        return;
    }

    contenedorResultados.innerHTML = productos.map(producto => {
        const nombre = String(producto.nombre_producto).replace(/'/g, "\\'");
        const presentacion = String(producto.presentacion).replace(/'/g, "\\'");
        const generico = producto.nombre_generico ?? '';
        const concentracion = producto.concentracion ?? '';

        return `
            <div
                class="exchange-result-item"
                onclick="seleccionarProductoNuevo(
                    ${producto.producto_presentacion_id},
                    '${nombre}',
                    '${presentacion}',
                    ${producto.precio_venta},
                    ${producto.stock_disponible}
                )"
            >
                <div>
                    <strong>${producto.nombre_producto}</strong>
                    <small>
                        ${generico} ${concentracion} ·
                        ${producto.presentacion} ·
                        Stock: ${producto.stock_disponible} ·
                        Precio: ${Number(producto.precio_venta).toFixed(2)} Bs
                    </small>
                </div>

                <i class="bi bi-plus-circle"></i>
            </div>
        `;
    }).join('');
});

function seleccionarProductoNuevo(id, nombre, presentacion, precio, stock) {
    inputPresentacionNueva.value = id;
    precioNuevo = Number(precio || 0);
    stockProductoNuevo = Number(stock || 0);

    textoProductoNuevo.textContent = `${nombre} - ${presentacion} | Precio: ${precioNuevo.toFixed(2)} Bs | Stock: ${stockProductoNuevo}`;
    boxProductoSeleccionado.style.display = 'flex';
    contenedorResultados.innerHTML = '';
    inputBuscador.value = '';

    inputCantidadNueva.max = stockProductoNuevo;

    if (Number(inputCantidadNueva.value) > stockProductoNuevo) {
        inputCantidadNueva.value = stockProductoNuevo;
    }

    calcularVistaPrevia();
}

window.seleccionarProductoNuevo = seleccionarProductoNuevo;

formCambioProducto.addEventListener('submit', function (event) {
    event.preventDefault();

    const detalleSeleccionado = obtenerDetalleSeleccionado();
    const productoNuevo = inputPresentacionNueva.value;
    const cantidadDevuelta = Number(inputCantidadDevuelta.value || 0);
    const cantidadNueva = Number(inputCantidadNueva.value || 0);
    const motivo = document.querySelector('textarea[name="motivo"]').value.trim();

    if (!detalleSeleccionado) {
        Swal.fire({
            icon: 'warning',
            title: 'Producto devuelto requerido',
            text: 'Debe seleccionar el producto que el cliente está devolviendo.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    if (cantidadDevuelta <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Cantidad devuelta inválida',
            text: 'La cantidad devuelta debe ser mayor a 0.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    if (!productoNuevo) {
        Swal.fire({
            icon: 'warning',
            title: 'Producto nuevo requerido',
            text: 'Debe seleccionar el producto nuevo que se entregará.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    if (cantidadNueva <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Cantidad nueva inválida',
            text: 'La cantidad nueva debe ser mayor a 0.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    if (stockProductoNuevo > 0 && cantidadNueva > stockProductoNuevo) {
        Swal.fire({
            icon: 'warning',
            title: 'Stock insuficiente',
            text: 'La cantidad nueva supera el stock disponible.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    if (motivo.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Motivo requerido',
            text: 'El motivo debe tener al menos 5 caracteres.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: '¿Confirmar cambio de producto?',
        text: 'Se devolverá stock del producto anterior, se descontará stock del nuevo producto y se ajustará caja si existe diferencia.',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280'
    }).then((result) => {
        if (result.isConfirmed) {
            event.target.submit();
        }
    });
});

calcularVistaPrevia();
</script>

@endif

@endsection