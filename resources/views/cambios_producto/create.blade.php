@extends('layouts.app')

@section('title', 'Cambio de producto | Santo Remedio')
@section('page-title', 'Cambio de producto')
@section('page-subtitle', 'Devolver un producto vendido y entregar otro producto')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Cambio de producto - Venta {{ $venta->numero_venta }}</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Seleccione el producto que el cliente devuelve y el producto nuevo que se entregará.
            </p>
        </div>

        <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
            Volver
        </a>
    </div>

    <div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 22px; margin-bottom: 0;">
        <div class="stat-card">
            <span>Total venta</span>
            <h3>{{ number_format($venta->total, 2) }} Bs</h3>
        </div>

        <div class="stat-card">
            <span>Cliente</span>
            <h3>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</h3>
        </div>

        <div class="stat-card">
            <span>Sucursal</span>
            <h3>{{ $venta->sucursal->nombre ?? '-' }}</h3>
        </div>

        <div class="stat-card">
            <span>Vendedor</span>
            <h3>{{ $venta->usuario->nombre ?? '-' }}</h3>
        </div>
    </div>
</div>

<div class="card">
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

    <form method="POST" action="{{ route('cambios-producto.store', $venta) }}" onsubmit="return confirmarFormulario(event, '¿Confirmar cambio de producto?')">
        @csrf

        <h3 style="margin-top: 0; color: #4C1D95;">1. Producto que el cliente devuelve</h3>

        <div class="table-container" style="margin-bottom: 22px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>Producto vendido</th>
                        <th>Presentación</th>
                        <th>Cantidad vendida</th>
                        <th>Ya devuelto/reembolsado</th>
                        <th>Disponible</th>
                        <th>Precio unitario</th>
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
                                    <small style="color: #6B7280;">
                                        {{ $detalle->producto->concentracion }}
                                    </small>
                                @endif

                                <br>
                                <small style="color: #6B7280;">
                                    Lotes:
                                    @forelse ($detalle->lotesDescontados as $loteDescontado)
                                        {{ $loteDescontado->lote->numero_lote ?? 'Sin lote' }}
                                        ({{ $loteDescontado->unidades_descontadas }} unidades)
                                    @empty
                                        Sin lote
                                    @endforelse
                                </small>
                            </td>

                            <td>{{ $detalle->productoPresentacion->nombre_mostrado ?? '-' }}</td>
                            <td>{{ $detalle->cantidad }}</td>
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

        <hr style="margin: 26px 0; border: none; border-top: 1px solid #E5E7EB;">

        <h3 style="margin-top: 0; color: #4C1D95;">2. Producto nuevo que se entregará</h3>

        <div class="form-group">
            <label>Buscar producto nuevo *</label>
            <input
                type="text"
                id="buscador_producto_nuevo"
                placeholder="Buscar por nombre, concentración o código de barras..."
                autocomplete="off"
            >

            <div id="resultados_producto_nuevo" style="margin-top: 10px;"></div>
        </div>

        <input type="hidden" name="producto_presentacion_nueva_id" id="producto_presentacion_nueva_id" value="{{ old('producto_presentacion_nueva_id') }}">

        <div id="producto_nuevo_seleccionado" class="alert-success" style="display: none; margin-bottom: 18px;">
            <strong>Producto nuevo seleccionado:</strong>
            <span id="nombre_producto_nuevo"></span>
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

        <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 22px;">
            <div class="stat-card">
                <span>Monto devuelto</span>
                <h3 id="monto_devuelto_preview">0.00 Bs</h3>
            </div>

            <div class="stat-card">
                <span>Monto producto nuevo</span>
                <h3 id="monto_nuevo_preview">0.00 Bs</h3>
            </div>

            <div class="stat-card">
                <span>Diferencia</span>
                <h3 id="diferencia_preview">0.00 Bs</h3>
                <small id="tipo_diferencia_preview" style="color: #6B7280;"></small>
            </div>
        </div>

        <div class="form-group" style="margin-top: 22px;">
            <label>Motivo del cambio *</label>
            <textarea
                name="motivo"
                rows="4"
                required
                placeholder="Ej: Cliente solicitó cambio por medicamento equivocado, presentación incorrecta, autorización de farmacia..."
            >{{ old('motivo') }}</textarea>
        </div>

        <div class="alert-danger" style="margin-top: 18px;">
            El sistema devolverá stock del producto anterior, descontará stock del producto nuevo y ajustará la caja si existe diferencia.
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-danger">
                Registrar cambio
            </button>

            <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
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

        contenedorResultados.innerHTML = productos.map(producto => `
            <div
                class="card"
                style="padding: 12px; margin-bottom: 8px; cursor: pointer;"
                onclick="seleccionarProductoNuevo(
                    ${producto.producto_presentacion_id},
                    '${String(producto.nombre_producto).replace(/'/g, "\\'")}',
                    '${String(producto.presentacion).replace(/'/g, "\\'")}',
                    ${producto.precio_venta},
                    ${producto.stock_disponible}
                )"
            >
                <strong>${producto.nombre_producto}</strong>
                <br>
                <small style="color: #6B7280;">
                    ${producto.nombre_generico ?? ''} ${producto.concentracion ?? ''} |
                    ${producto.presentacion} |
                    Stock: ${producto.stock_disponible} |
                    Precio: ${Number(producto.precio_venta).toFixed(2)} Bs
                </small>
            </div>
        `).join('');
    });

    function seleccionarProductoNuevo(id, nombre, presentacion, precio, stock) {
        inputPresentacionNueva.value = id;
        precioNuevo = Number(precio || 0);
        stockProductoNuevo = Number(stock || 0);

        textoProductoNuevo.textContent = `${nombre} - ${presentacion} | Precio: ${precioNuevo.toFixed(2)} Bs | Stock: ${stockProductoNuevo}`;
        boxProductoSeleccionado.style.display = 'block';
        contenedorResultados.innerHTML = '';
        inputBuscador.value = '';

        inputCantidadNueva.max = stockProductoNuevo;

        if (Number(inputCantidadNueva.value) > stockProductoNuevo) {
            inputCantidadNueva.value = stockProductoNuevo;
        }

        calcularVistaPrevia();
    }

    window.seleccionarProductoNuevo = seleccionarProductoNuevo;

    calcularVistaPrevia();
</script>

@endsection