@extends('layouts.app')

@section('title', 'Nueva venta | Santo Remedio')
@section('page-title', 'Nueva venta')
@section('page-subtitle', 'Venta rápida con carrito')

@section('content')

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

<form method="POST" action="{{ route('ventas.store') }}" id="form_venta" class="sale-form">
    @csrf

    <div class="sale-layout">

        <section class="sale-main">

            <div class="sale-header-card">
                <div>
                    <h2>
                        <i class="bi bi-upc-scan"></i>
                        Venta rápida
                    </h2>

                    <p>
                        Sucursal: <strong>{{ $sucursal->nombre }}</strong> ·
                        Caja: <strong>{{ $cajaAbierta->turno->nombre ?? 'Sin turno' }}</strong> ·
                        Apertura: <strong>{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</strong>
                    </p>
                </div>
            </div>

            <div class="sale-search-card">
                <div class="form-group sale-search-group">
                    <label>Buscar producto o escanear código</label>
                    <div class="sale-search-box">
                        <i class="bi bi-search"></i>
                        <input
                            type="text"
                            id="buscador_producto"
                            placeholder="Producto, presentación, laboratorio o código de barras"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div id="resultados_busqueda" class="sale-results" style="display: none;"></div>
            </div>

            <div class="sale-cart-card">
                <div class="sale-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-cart-check"></i>
                            Carrito de venta
                        </h3>
                        <small>Productos y promociones agregadas</small>
                    </div>
                </div>

                <div class="table-container sale-table-container">
                    <table class="table sale-table" id="tabla_carrito">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Forma</th>
                                <th>Stock</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="carrito_vacio">
                                <td colspan="7" class="sale-empty">
                                    No hay productos agregados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <aside class="sale-summary">

            <div class="sale-summary-card">
                <h3>
                    <i class="bi bi-person"></i>
                    Cliente
                </h3>

                <div class="form-group">
                    <label>Cliente registrado</label>
                    <select name="cliente_id" id="cliente_id">
                        <option value="">Consumidor final</option>

                        @foreach ($clientes as $cliente)
                            <option
                                value="{{ $cliente->id }}"
                                data-descuento="{{ $cliente->descuento_default }}"
                                {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                                @if ($cliente->ci_nit)
                                    - CI/NIT: {{ $cliente->ci_nit }}
                                @endif
                            </option>
                        @endforeach
                    </select>

                    @error('cliente_id')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <small class="sale-help">
                    Si no selecciona cliente, se registrará como consumidor final.
                </small>
            </div>

            <div class="sale-summary-card sale-payment-card">
                <h3>
                    <i class="bi bi-cash-coin"></i>
                    Pago
                </h3>

                <div class="sale-payment-grid">
                    <div class="form-group">
                        <label>Método *</label>
                        <select name="metodo_pago_id" required>
                            <option value="">Seleccione</option>
                            @foreach ($metodosPago as $metodo)
                                <option value="{{ $metodo->id }}" @selected(old('metodo_pago_id') == $metodo->id)>
                                    {{ $metodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Descuento %</label>

                        @if (auth()->user()->tienePermiso('aplicar_descuento'))
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="descuento_porcentaje"
                                id="descuento_porcentaje"
                                value="{{ old('descuento_porcentaje', 0) }}"
                            >
                        @else
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                id="descuento_porcentaje"
                                value="0"
                                disabled
                            >

                            <input type="hidden" name="descuento_porcentaje" value="0">
                        @endif
                    </div>

                    <div class="form-group sale-full">
                        <label>Monto recibido *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="monto_recibido"
                            id="monto_recibido"
                            value="{{ old('monto_recibido', 0) }}"
                            required
                        >
                    </div>
                </div>

                <div class="sale-totals">
                    <div class="sale-total-item">
                        <span>Subtotal</span>
                        <strong id="subtotal_venta">0.00 Bs</strong>
                    </div>

                    <div class="sale-total-item">
                        <span>Descuento</span>
                        <strong id="descuento_venta">0.00 Bs</strong>
                    </div>

                    <div class="sale-total-item sale-total-main">
                        <span>Total</span>
                        <strong id="total_venta">0.00 Bs</strong>
                    </div>

                    <div class="sale-total-item">
                        <span>Cambio</span>
                        <strong id="cambio_venta">0.00 Bs</strong>
                    </div>
                </div>
            </div>

            <div class="sale-summary-card">
                <div class="form-group">
                    <label>Observación</label>
                    <textarea name="observacion" rows="2" placeholder="Opcional">{{ old('observacion') }}</textarea>
                </div>
            </div>

            <div id="inputs_carrito"></div>

            <div class="sale-actions">
                <a href="{{ route('ventas.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>

                @if (auth()->user()->tienePermiso('realizar_venta'))
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Guardar venta
                    </button>
                @endif
            </div>

        </aside>

    </div>
</form>
@php
    $itemsAntiguosVenta = collect(old('items', []))->map(function ($item) {
        return [
            'id' => $item['producto_presentacion_id'] ?? null,
            'tipo_item' => 'producto',
            'producto_id' => $item['producto_id'] ?? null,

            'nombre' => $item['nombre'] ?? 'Producto seleccionado',
            'nombre_mostrado' => $item['nombre_mostrado'] ?? 'Producto seleccionado',
            'generico' => $item['generico'] ?? '',
            'concentracion' => $item['concentracion'] ?? '',
            'laboratorio' => $item['laboratorio'] ?? '',
            'tipo_producto' => $item['tipo_producto'] ?? '',
            'presentacion' => $item['presentacion'] ?? '',

            'precio_venta' => (float) ($item['precio_venta'] ?? 0),
            'unidades_equivalentes' => max((int) ($item['unidades_equivalentes'] ?? 1), 1),
            'stock_disponible' => (int) ($item['stock_disponible'] ?? 0),
            'stock_aproximado_presentacion' => (int) ($item['stock_aproximado_presentacion'] ?? 0),
            'cantidad' => max((int) ($item['cantidad'] ?? 1), 1),
        ];
    })->filter(function ($item) {
        return !empty($item['id']);
    })->values();

    $promocionesAntiguasVenta = collect(old('promociones', []))->map(function ($item) {
        return [
            'id' => 'promo_' . ($item['promocion_id'] ?? ''),
            'tipo_item' => 'promocion',
            'promocion_id' => $item['promocion_id'] ?? null,

            'nombre_mostrado' => $item['nombre_mostrado'] ?? 'Promoción seleccionada',
            'presentacion' => 'Promoción',
            'laboratorio' => '',
            'concentracion' => $item['concentracion'] ?? '',
            'precio_venta' => (float) ($item['precio_venta'] ?? 0),
            'unidades_equivalentes' => 1,
            'stock_disponible' => (int) ($item['stock_disponible'] ?? 0),
            'cantidad' => max((int) ($item['cantidad'] ?? 1), 1),
            'items_promocion' => $item['items_promocion'] ?? [],
        ];
    })->filter(function ($item) {
        return !empty($item['promocion_id']);
    })->values();

    $carritoAntiguoVenta = $itemsAntiguosVenta
        ->concat($promocionesAntiguasVenta)
        ->values();
@endphp
<script>
    const buscarUrl = "{{ route('ventas.buscar-productos') }}";
    const buscarPromocionesUrl = "{{ route('ventas.buscar-promociones') }}";

    const buscador = document.getElementById('buscador_producto');
    const resultados = document.getElementById('resultados_busqueda');
    const tablaCarrito = document.querySelector('#tabla_carrito tbody');
    const carritoVacio = document.getElementById('carrito_vacio');
    const inputsCarrito = document.getElementById('inputs_carrito');

    const totalVentaText = document.getElementById('total_venta');
    const montoRecibidoInput = document.getElementById('monto_recibido');
   
    const cambioVentaText = document.getElementById('cambio_venta');
    const clienteSelect = document.getElementById('cliente_id');
    const descuentoInput = document.getElementById('descuento_porcentaje');
    const puedeAplicarDescuento = @json(auth()->user()->tienePermiso('aplicar_descuento'));
    const subtotalVentaText = document.getElementById('subtotal_venta');
    const descuentoVentaText = document.getElementById('descuento_venta');

    let carritoAntiguoVenta = @json($carritoAntiguoVenta);
    let carrito = carritoAntiguoVenta.length ? carritoAntiguoVenta : [];

    let timeoutBusqueda = null;

    buscador.addEventListener('input', function () {
        clearTimeout(timeoutBusqueda);

        const termino = this.value.trim();

        if (termino.length < 2) {
            resultados.style.display = 'none';
            resultados.innerHTML = '';
            return;
        }

        timeoutBusqueda = setTimeout(() => {
            buscarProductos(termino);
        }, 250);
    });

    async function buscarProductos(termino) {
        try {
            const [responseProductos, responsePromociones] = await Promise.all([
                fetch(`${buscarUrl}?busqueda=${encodeURIComponent(termino)}`),
                fetch(`${buscarPromocionesUrl}?busqueda=${encodeURIComponent(termino)}`)
            ]);

            const productos = await responseProductos.json();
            const promociones = await responsePromociones.json();

            mostrarResultados(productos, promociones);
        } catch (error) {
            resultados.style.display = 'block';
            resultados.innerHTML = '<p style="color:#991B1B;">Error al buscar productos o promociones.</p>';
        }
    }

    function mostrarResultados(productos, promociones = []) {
        resultados.style.display = 'block';

        const hayProductos = Array.isArray(productos) && productos.length > 0;
        const hayPromociones = Array.isArray(promociones) && promociones.length > 0;

        if (!hayProductos && !hayPromociones) {
            resultados.innerHTML = '<p style="color:#6B7280;">No se encontraron productos ni promociones disponibles.</p>';
            return;
        }

        let html = '';

        if (hayPromociones) {
            html += `
                <div style="padding: 8px 12px; color:#4C1D95; font-weight:bold;">
                    Promociones disponibles
                </div>
            `;

            html += promociones.map(promocion => {
                const dataPromocion = encodeURIComponent(JSON.stringify(promocion));

                const itemsTexto = promocion.items.map(item => {
                    return `${item.producto} x${item.cantidad}`;
                }).join(' + ');

                return `
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #E5E7EB; background:#F5F3FF;">
                        <div>
                            <strong>${promocion.nombre}</strong>
                            <div style="color:#6B7280; font-size:13px; margin-top:3px;">
                                ${itemsTexto}
                            </div>
                            <div style="color:#4C1D95; font-size:13px; margin-top:3px;">
                                Precio promoción: ${Number(promocion.precio_promocional).toFixed(2)} Bs —
                                Stock combo: ${Number(promocion.stock_promocion || 0)}
                            </div>
                        </div>

                        <button type="button" class="btn-primary" data-promocion="${dataPromocion}" onclick="agregarPromocionDesdeBoton(this)">
                            Agregar promo
                        </button>
                    </div>
                `;
            }).join('');
        }

        if (hayProductos) {
            html += `
                <div style="padding: 8px 12px; color:#4C1D95; font-weight:bold;">
                    Productos
                </div>
            `;

            html += productos.map(producto => {
                const nombreVisible = producto.nombre_mostrado || producto.presentacion || producto.nombre;
                const laboratorio = producto.laboratorio ? ` | ${producto.laboratorio}` : '';
                const tipo = producto.tipo_producto ? producto.tipo_producto.replace('_', ' ') : '';
                const stockBase = Number(producto.stock_disponible || 0);
                const stockPresentacion = Number(producto.stock_aproximado_presentacion || 0);
                const unidades = Number(producto.unidades_equivalentes || 1);

                let stockTexto = `Stock: ${stockBase} unidad(es)`;

                if (unidades > 1) {
                    stockTexto += ` | Aprox: ${stockPresentacion} disponible(s)`;
                }

                const dataProducto = encodeURIComponent(JSON.stringify(producto));

                return `
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #E5E7EB;">
                        <div>
                            <strong>${nombreVisible}</strong>

                            <div style="color:#6B7280; font-size:13px; margin-top: 3px;">
                                ${tipo}${laboratorio}
                            </div>

                            <div style="color:#4C1D95; font-size:13px; margin-top: 3px;">
                                Precio: ${Number(producto.precio_venta).toFixed(2)} Bs —
                                ${stockTexto}
                            </div>
                        </div>

                        <button type="button" class="btn-primary" data-producto="${dataProducto}" onclick="agregarProductoDesdeBoton(this)">
                            Agregar
                        </button>
                    </div>
                `;
            }).join('');
        }

        resultados.innerHTML = html;
    }

    function agregarProductoDesdeBoton(boton) {
        try {
            const producto = JSON.parse(decodeURIComponent(boton.dataset.producto));
            agregarAlCarrito(producto);
        } catch (error) {
            console.error('Error al seleccionar producto:', error);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo seleccionar el producto.',
                confirmButtonColor: '#6D28D9'
            });
        }
    }

    function agregarAlCarrito(producto) {
        const existente = carrito.find(item => String(item.id) === String(producto.id));

        if (existente) {
            existente.cantidad += 1;
        } else {
            carrito.push({
            id: producto.id,
            tipo_item: 'producto',
            producto_id: producto.producto_id,
            nombre: producto.nombre,
            nombre_mostrado: producto.nombre_mostrado || producto.presentacion || producto.nombre,
            generico: producto.generico,
            concentracion: producto.concentracion,
            laboratorio: producto.laboratorio || '',
            tipo_producto: producto.tipo_producto || '',
            presentacion: producto.presentacion,
            precio_venta: Number(producto.precio_venta),
            unidades_equivalentes: Number(producto.unidades_equivalentes),
            stock_disponible: Number(producto.stock_disponible),
            stock_aproximado_presentacion: Number(producto.stock_aproximado_presentacion || 0),
            cantidad: 1
        });
        }

        buscador.value = '';
        resultados.style.display = 'none';
        resultados.innerHTML = '';

        renderCarrito();
    }

    function agregarPromocionAlCarrito(promocion) {
        const idPromo = 'promo_' + promocion.id;

        const existente = carrito.find(item => item.id === idPromo);

        if (existente) {
            existente.cantidad += 1;
        } else {
            carrito.push({
                id: idPromo,
                tipo_item: 'promocion',
                promocion_id: promocion.id,
                nombre_mostrado: promocion.nombre,
                presentacion: 'Promoción',
                laboratorio: '',
                concentracion: promocion.items_count + ' producto(s) incluido(s)',
                precio_venta: Number(promocion.precio_promocional),
                unidades_equivalentes: 1,
                stock_disponible: Number(promocion.stock_promocion || 0),
                cantidad: 1,
                items_promocion: promocion.items
            });
        }

        buscador.value = '';
        resultados.style.display = 'none';
        resultados.innerHTML = '';

        renderCarrito();
    }

    function agregarPromocionDesdeBoton(boton) {
        try {
            const promocion = JSON.parse(decodeURIComponent(boton.dataset.promocion));
            agregarPromocionAlCarrito(promocion);
        } catch (error) {
            console.error('Error al seleccionar promoción:', error);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo seleccionar la promoción.',
                confirmButtonColor: '#6D28D9'
            });
        }
    }

    function cambiarCantidad(id, cantidad) {
        cantidad = parseInt(cantidad || 1);

        const item = carrito.find(producto => String(producto.id) === String(id));

        if (!item) return;

        if (cantidad < 1) cantidad = 1;

        if (item.tipo_item === 'promocion') {
            if (cantidad > item.stock_disponible) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock insuficiente',
                    text: `Solo hay ${item.stock_disponible} promoción(es) disponible(s).`,
                    confirmButtonColor: '#6D28D9'
                });

                cantidad = item.stock_disponible;
            }

            item.cantidad = cantidad;
            renderCarrito();
            return;
        }

        const unidadesNecesarias = cantidad * item.unidades_equivalentes;

        if (unidadesNecesarias > item.stock_disponible) {
            Swal.fire({
                icon: 'warning',
                title: 'Stock insuficiente',
                text: 'No hay stock suficiente para esa cantidad.'
            });
            cantidad = Math.floor(item.stock_disponible / item.unidades_equivalentes);
            if (cantidad < 1) cantidad = 1;
        }

        item.cantidad = cantidad;
        renderCarrito();
    }

    function quitarProducto(id) {
        carrito = carrito.filter(item => String(item.id) !== String(id));
        renderCarrito();
    }

    function renderCarrito() {
        tablaCarrito.innerHTML = '';
        inputsCarrito.innerHTML = '';

        if (carrito.length === 0) {
            tablaCarrito.appendChild(carritoVacio);
            carritoVacio.style.display = '';
            actualizarTotales();
            return;
        }

        carritoVacio.style.display = 'none';

        carrito.forEach((item, index) => {
            const subtotal = item.cantidad * item.precio_venta;

            tablaCarrito.innerHTML += `
                <tr>
                    <td>
                        <strong>${item.nombre_mostrado}</strong>
                        <br>
                        <small style="color:#6B7280;">
                            ${item.laboratorio ? item.laboratorio + ' | ' : ''}
                            ${item.concentracion ?? ''}
                        </small>
                    </td>
                    <td>${item.presentacion || '-'}</td>
                    <td>
                        ${item.stock_disponible}
                        ${item.unidades_equivalentes > 1
                            ? `<br><small style="color:#6B7280;">Aprox: ${Math.floor(item.stock_disponible / item.unidades_equivalentes)}</small>`
                            : ''
                        }
                    </td>
                    <td>
                        <input
                            type="number"
                            min="1"
                            value="${item.cantidad}"
                            style="width:80px;"
                            onchange="cambiarCantidad('${item.id}', this.value)"
                        >
                    </td>
                    <td>${item.precio_venta.toFixed(2)} Bs</td>
                    <td>${subtotal.toFixed(2)} Bs</td>
                    <td>
                        <button type="button" class="icon-action icon-action-danger" onclick="quitarProducto('${item.id}')" title="Quitar">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            `;

            if (item.tipo_item === 'promocion') {
                inputsCarrito.innerHTML += `
                    <input type="hidden" name="promociones[${index}][promocion_id]" value="${item.promocion_id}">
                    <input type="hidden" name="promociones[${index}][cantidad]" value="${item.cantidad}">

                    <input type="hidden" name="promociones[${index}][nombre_mostrado]" value="${item.nombre_mostrado || ''}">
                    <input type="hidden" name="promociones[${index}][concentracion]" value="${item.concentracion || ''}">
                    <input type="hidden" name="promociones[${index}][precio_venta]" value="${item.precio_venta || 0}">
                    <input type="hidden" name="promociones[${index}][stock_disponible]" value="${item.stock_disponible || 0}">
                `;
            } else {
                inputsCarrito.innerHTML += `
                    <input type="hidden" name="items[${index}][producto_presentacion_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][producto_id]" value="${item.producto_id || ''}">

                    <input type="hidden" name="items[${index}][nombre]" value="${item.nombre || ''}">
                    <input type="hidden" name="items[${index}][nombre_mostrado]" value="${item.nombre_mostrado || ''}">
                    <input type="hidden" name="items[${index}][generico]" value="${item.generico || ''}">
                    <input type="hidden" name="items[${index}][concentracion]" value="${item.concentracion || ''}">
                    <input type="hidden" name="items[${index}][laboratorio]" value="${item.laboratorio || ''}">
                    <input type="hidden" name="items[${index}][tipo_producto]" value="${item.tipo_producto || ''}">
                    <input type="hidden" name="items[${index}][presentacion]" value="${item.presentacion || ''}">

                    <input type="hidden" name="items[${index}][precio_venta]" value="${item.precio_venta || 0}">
                    <input type="hidden" name="items[${index}][unidades_equivalentes]" value="${item.unidades_equivalentes || 1}">
                    <input type="hidden" name="items[${index}][stock_disponible]" value="${item.stock_disponible || 0}">
                    <input type="hidden" name="items[${index}][stock_aproximado_presentacion]" value="${item.stock_aproximado_presentacion || 0}">
                    <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
                `;
            }
        });

        actualizarTotales();
    }

    function actualizarTotales() {
        const subtotal = carrito.reduce((sum, item) => {
            return sum + (item.cantidad * item.precio_venta);
        }, 0);

        let descuentoPorcentaje = Number(descuentoInput.value || 0);

        if (descuentoPorcentaje < 0) descuentoPorcentaje = 0;
        if (descuentoPorcentaje > 100) descuentoPorcentaje = 100;

        const descuentoMonto = subtotal * (descuentoPorcentaje / 100);
        const total = subtotal - descuentoMonto;

        const montoRecibido = Number(montoRecibidoInput.value || 0);
        const cambio = montoRecibido - total;

        subtotalVentaText.textContent = subtotal.toFixed(2) + ' Bs';
        descuentoVentaText.textContent = descuentoMonto.toFixed(2) + ' Bs';
        totalVentaText.textContent = total.toFixed(2) + ' Bs';
        cambioVentaText.textContent = cambio > 0 ? cambio.toFixed(2) + ' Bs' : '0.00 Bs';
    }

    montoRecibidoInput.addEventListener('input', actualizarTotales);

    descuentoInput.addEventListener('input', actualizarTotales);

    

    clienteSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        const descuentoCliente = Number(option.getAttribute('data-descuento') || 0);

        if (puedeAplicarDescuento) {
            descuentoInput.value = descuentoCliente;
        } else {
            descuentoInput.value = 0;
        }

        actualizarTotales();
    });

    document.getElementById('form_venta').addEventListener('submit', function (event) {
        if (carrito.length === 0) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Carrito vacío',
                text: 'Debe agregar al menos un producto.'
            });
        }
    });

    renderCarrito();
</script>

@endsection