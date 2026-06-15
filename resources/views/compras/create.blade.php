@extends('layouts.app')

@section('title', 'Nueva compra | Santo Remedio')
@section('page-title', 'Nueva compra')
@section('page-subtitle', 'Registrar compra a proveedor e ingresar stock al inventario')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Registrar compra</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Sucursal: <strong>{{ $sucursal->nombre }}</strong>.
            Esta compra ingresará productos al inventario de la sucursal actual.
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

    <form method="POST" action="{{ route('compras.store') }}" id="form_compra">
        @csrf

        <div class="card" style="margin-bottom: 22px; background: #FAFAFA;">
            <h3 style="margin-top: 0; color: #4C1D95;">Proveedor y pago</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label>Proveedor *</label>
                    <select name="proveedor_id" required>
                        <option value="">Seleccione proveedor</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                {{ $proveedor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Tipo de pago *</label>
                    <select name="tipo_pago" id="tipo_pago" required>
                        <option value="contado" {{ old('tipo_pago') === 'contado' ? 'selected' : '' }}>Contado</option>
                        <option value="credito" {{ old('tipo_pago') === 'credito' ? 'selected' : '' }}>Crédito</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Monto pagado *</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="monto_pagado"
                        id="monto_pagado"
                        value="{{ old('monto_pagado', 0) }}"
                        required
                    >
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px;">
                <label>Observación</label>
                <textarea name="observacion" rows="3" placeholder="Opcional">{{ old('observacion') }}</textarea>
            </div>
        </div>

        <div class="card" style="margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Agregar producto</h3>

            <div class="form-group">
                <label>Buscar producto o escanear código</label>
                <input
                    type="text"
                    id="buscador_producto_compra"
                    placeholder="Ej: paracetamol, 500 mg o código de barras"
                    autocomplete="off"
                >
            </div>

            <div id="resultados_producto_compra" class="card" style="display: none; margin-top: 12px; background: #FAFAFA;"></div>

            <input type="hidden" id="producto_presentacion_id">

            <div id="producto_seleccionado_box" style="display: none; margin-top: 16px; padding: 14px; background: #F5F3FF; border-radius: 12px;">
                <strong id="producto_seleccionado_nombre"></strong>
                <br>
                <small id="producto_seleccionado_detalle" style="color: #6B7280;"></small>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group">
                    <label>Cantidad *</label>
                    <input type="number" id="cantidad" min="1" value="1">
                </div>

                <div class="form-group">
                    <label>Precio compra *</label>
                    <input type="number" id="precio_compra" step="0.01" min="0" value="0">
                </div>

                <div class="form-group">
                    <label>N° lote</label>
                    <input type="text" id="numero_lote" placeholder="Opcional. Ej: L001">
                    <small style="color: #6B7280;">
                        Si se deja vacío, el sistema generará un lote interno automáticamente.
                    </small>
                </div>

                <div class="form-group">
                    <label>Fecha vencimiento</label>
                    <input type="date" id="fecha_vencimiento">
                </div>
            </div>

            <div style="margin-top: 14px;">
                <button type="button" class="btn-primary" onclick="agregarProductoCompra()">
                    Agregar a compra
                </button>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top: 0; color: #4C1D95;">Detalle de compra</h3>

            <div class="table-container">
                <table class="table" id="tabla_compra">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Presentación</th>
                            <th>Lote</th>
                            <th>Vencimiento</th>
                            <th>Cantidad</th>
                            <th>Unidades</th>
                            <th>Precio compra</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="compra_vacia">
                            <td colspan="9" style="text-align: center; color: #6B7280;">
                                No hay productos agregados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 22px;">
            <div class="stat-card">
                <span>Total compra</span>
                <h3 id="total_compra">0.00 Bs</h3>
            </div>

            <div class="stat-card">
                <span>Monto pagado</span>
                <h3 id="monto_pagado_text">0.00 Bs</h3>
            </div>

            <div class="stat-card">
                <span>Saldo pendiente</span>
                <h3 id="saldo_pendiente">0.00 Bs</h3>
            </div>
        </div>

        <div id="inputs_compra"></div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">
                Guardar compra
            </button>

            <a href="{{ route('compras.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
    const buscarProductosCompraUrl = "{{ route('compras.buscar-productos') }}";

    const buscadorProductoCompra = document.getElementById('buscador_producto_compra');
    const resultadosProductoCompra = document.getElementById('resultados_producto_compra');

    const productoPresentacionInput = document.getElementById('producto_presentacion_id');
    const productoSeleccionadoBox = document.getElementById('producto_seleccionado_box');
    const productoSeleccionadoNombre = document.getElementById('producto_seleccionado_nombre');
    const productoSeleccionadoDetalle = document.getElementById('producto_seleccionado_detalle');

    const cantidadInput = document.getElementById('cantidad');
    const precioCompraInput = document.getElementById('precio_compra');
    const numeroLoteInput = document.getElementById('numero_lote');
    const fechaVencimientoInput = document.getElementById('fecha_vencimiento');

    const tablaCompra = document.querySelector('#tabla_compra tbody');
    const compraVacia = document.getElementById('compra_vacia');
    const inputsCompra = document.getElementById('inputs_compra');

    const montoPagadoInput = document.getElementById('monto_pagado');
    const totalCompraText = document.getElementById('total_compra');
    const montoPagadoText = document.getElementById('monto_pagado_text');
    const saldoPendienteText = document.getElementById('saldo_pendiente');

    let compraItems = [];
    let productoSeleccionado = null;
    let timeoutBusquedaCompra = null;

    buscadorProductoCompra.addEventListener('input', function () {
        clearTimeout(timeoutBusquedaCompra);

        const termino = this.value.trim();

        if (termino.length < 2) {
            resultadosProductoCompra.style.display = 'none';
            resultadosProductoCompra.innerHTML = '';
            return;
        }

        timeoutBusquedaCompra = setTimeout(() => {
            buscarProductosCompra(termino);
        }, 250);
    });

    async function buscarProductosCompra(termino) {
        try {
            const response = await fetch(`${buscarProductosCompraUrl}?busqueda=${encodeURIComponent(termino)}`);
            const data = await response.json();

            mostrarResultadosProductosCompra(data);
        } catch (error) {
            resultadosProductoCompra.style.display = 'block';
            resultadosProductoCompra.innerHTML = '<p style="color:#991B1B;">Error al buscar productos.</p>';
        }
    }

    function mostrarResultadosProductosCompra(productos) {
        resultadosProductoCompra.style.display = 'block';

        if (!Array.isArray(productos) || productos.length === 0) {
            resultadosProductoCompra.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                    <p style="color:#6B7280; margin: 0;">
                        No se encontraron productos.
                    </p>
                    <button type="button" class="btn-secondary" onclick="productoNoEncontrado()">
                        Crear producto rápido
                    </button>
                </div>
            `;
            return;
        }

        resultadosProductoCompra.innerHTML = productos.map(producto => {
            const dataProducto = JSON.stringify(producto).replace(/'/g, '&#39;');

            return `
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #E5E7EB;">
                    <div>
                        <strong>${producto.nombre}</strong>
                        <div style="color:#6B7280; font-size:13px;">
                            ${producto.generico ?? ''} ${producto.concentracion ?? ''}
                            / ${producto.presentacion}
                        </div>
                        <div style="color:#4C1D95; font-size:13px;">
                            Precio compra sugerido: ${Number(producto.precio_compra).toFixed(2)} Bs —
                            Precio venta: ${Number(producto.precio_venta).toFixed(2)} Bs
                        </div>
                    </div>

                    <button type="button" class="btn-primary" onclick='seleccionarProductoCompra(${dataProducto})'>
                        Seleccionar
                    </button>
                </div>
            `;
        }).join('');
    }

    function seleccionarProductoCompra(producto) {
        productoSeleccionado = producto;

        productoPresentacionInput.value = producto.id;
        precioCompraInput.value = Number(producto.precio_compra || 0).toFixed(2);

        productoSeleccionadoNombre.textContent = producto.nombre;
        productoSeleccionadoDetalle.textContent = `${producto.concentracion ?? ''} / ${producto.presentacion} / Unidades: ${producto.unidades_equivalentes}`;

        productoSeleccionadoBox.style.display = 'block';

        buscadorProductoCompra.value = '';
        resultadosProductoCompra.style.display = 'none';
        resultadosProductoCompra.innerHTML = '';
    }

    function productoNoEncontrado() {
        Swal.fire({
            icon: 'info',
            title: 'Producto no registrado',
            text: 'Luego agregaremos la creación rápida de productos desde esta pantalla.',
            confirmButtonColor: '#6D28D9'
        });
    }

    function agregarProductoCompra() {
        if (!productoSeleccionado || !productoPresentacionInput.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione un producto',
                text: 'Debe buscar y seleccionar un producto para agregarlo a la compra.',
                confirmButtonColor: '#6D28D9'
            });
            return;
        }

        const cantidad = parseInt(cantidadInput.value || 1);
        const precioCompra = Number(precioCompraInput.value || 0);

        if (cantidad < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Cantidad inválida',
                text: 'La cantidad debe ser mayor a cero.',
                confirmButtonColor: '#6D28D9'
            });
            return;
        }

        if (precioCompra < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Precio inválido',
                text: 'El precio de compra no puede ser negativo.',
                confirmButtonColor: '#6D28D9'
            });
            return;
        }

        const item = {
            producto_presentacion_id: Number(productoSeleccionado.id),
            nombre: productoSeleccionado.nombre,
            concentracion: productoSeleccionado.concentracion || '',
            presentacion: productoSeleccionado.presentacion,
            unidades_equivalentes: Number(productoSeleccionado.unidades_equivalentes || 1),
            cantidad: cantidad,
            precio_compra: precioCompra,
            numero_lote: numeroLoteInput.value,
            fecha_vencimiento: fechaVencimientoInput.value
        };

        compraItems.push(item);

        limpiarProductoSeleccionado();
        renderCompra();
    }

    function limpiarProductoSeleccionado() {
        productoSeleccionado = null;
        productoPresentacionInput.value = '';
        productoSeleccionadoBox.style.display = 'none';
        productoSeleccionadoNombre.textContent = '';
        productoSeleccionadoDetalle.textContent = '';

        cantidadInput.value = 1;
        precioCompraInput.value = 0;
        numeroLoteInput.value = '';
        fechaVencimientoInput.value = '';

        buscadorProductoCompra.focus();
    }

    function quitarProductoCompra(index) {
        compraItems.splice(index, 1);
        renderCompra();
    }

    function renderCompra() {
        tablaCompra.innerHTML = '';
        inputsCompra.innerHTML = '';

        if (compraItems.length === 0) {
            tablaCompra.appendChild(compraVacia);
            compraVacia.style.display = '';
            actualizarTotalesCompra();
            return;
        }

        compraVacia.style.display = 'none';

        compraItems.forEach((item, index) => {
            const unidades = item.cantidad * item.unidades_equivalentes;
            const subtotal = item.cantidad * item.precio_compra;

            tablaCompra.innerHTML += `
                <tr>
                    <td>
                        ${item.nombre}
                        <br>
                        <small style="color:#6B7280;">${item.concentracion}</small>
                    </td>
                    <td>${item.presentacion}</td>
                    <td>${item.numero_lote || 'Sin lote'}</td>
                    <td>${item.fecha_vencimiento || '-'}</td>
                    <td>${item.cantidad}</td>
                    <td>${unidades}</td>
                    <td>${item.precio_compra.toFixed(2)} Bs</td>
                    <td>${subtotal.toFixed(2)} Bs</td>
                    <td>
                        <button type="button" class="btn-danger" onclick="quitarProductoCompra(${index})">
                            Quitar
                        </button>
                    </td>
                </tr>
            `;

            inputsCompra.innerHTML += `
                <input type="hidden" name="items[${index}][producto_presentacion_id]" value="${item.producto_presentacion_id}">
                <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
                <input type="hidden" name="items[${index}][precio_compra]" value="${item.precio_compra}">
                <input type="hidden" name="items[${index}][numero_lote]" value="${item.numero_lote}">
                <input type="hidden" name="items[${index}][fecha_vencimiento]" value="${item.fecha_vencimiento}">
            `;
        });

        actualizarTotalesCompra();
    }

    function actualizarTotalesCompra() {
        const total = compraItems.reduce((sum, item) => {
            return sum + (item.cantidad * item.precio_compra);
        }, 0);

        const montoPagado = Number(montoPagadoInput.value || 0);
        const saldo = total - montoPagado;

        totalCompraText.textContent = total.toFixed(2) + ' Bs';
        montoPagadoText.textContent = montoPagado.toFixed(2) + ' Bs';
        saldoPendienteText.textContent = saldo > 0 ? saldo.toFixed(2) + ' Bs' : '0.00 Bs';
    }

    montoPagadoInput.addEventListener('input', actualizarTotalesCompra);

    document.getElementById('form_compra').addEventListener('submit', function (event) {
        if (compraItems.length === 0) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Compra vacía',
                text: 'Debe agregar al menos un producto a la compra.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        const total = compraItems.reduce((sum, item) => {
            return sum + (item.cantidad * item.precio_compra);
        }, 0);

        const montoPagado = Number(montoPagadoInput.value || 0);

        if (montoPagado > total) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Monto pagado incorrecto',
                text: 'El monto pagado no puede ser mayor al total de la compra.',
                confirmButtonColor: '#6D28D9'
            });
        }
    });
</script>

@endsection