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
                    <select name="proveedor_id" id="proveedor_id" required>
                        <option value="">Seleccione proveedor...</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                        @endforeach
                    </select>

                    <button type="button" class="btn-secondary" style="margin-top: 8px;" onclick="crearProveedorRapido()">
                        + Nuevo proveedor
                    </button>
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

            <div id="resultados_producto_compra" class="card" style="display: none; margin-top: 12px; background: #FAFAFA;">

            </div>
            <button type="button" class="btn-primary" onclick="mostrarFormularioProductoRapido()">
                + Crear producto rápido
            </button>

            <div id="modal_producto_rapido" class="modal-overlay" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h3>Crear producto rápido</h3>
                            <p>Registre un producto nuevo sin salir de la compra.</p>
                        </div>

                        <button type="button" class="modal-close" onclick="ocultarFormularioProductoRapido()">
                            ✕
                        </button>
                    </div>

                    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="form-group">
                            <label>Nombre comercial *</label>
                            <input type="text" id="rapido_nombre_comercial" placeholder="Ej: Paracetamol">
                        </div>

                        <div class="form-group">
                            <label>Nombre genérico</label>
                            <input type="text" id="rapido_nombre_generico" placeholder="Ej: Acetaminofén">
                        </div>

                        <div class="form-group">
                            <label>Concentración</label>
                            <input type="text" id="rapido_concentracion" placeholder="Ej: 500 mg">
                        </div>
                    </div>

                    <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select id="rapido_categoria_id">
                                <option value="">Sin categoría</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>

                            <button type="button" class="btn-secondary" style="margin-top: 8px;" onclick="crearCategoriaRapida()">
                                + Nueva categoría
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Laboratorio</label>
                            <select id="rapido_laboratorio_id">
                                <option value="">Sin laboratorio</option>
                                @foreach ($laboratorios as $laboratorio)
                                    <option value="{{ $laboratorio->id }}">{{ $laboratorio->nombre }}</option>
                                @endforeach
                            </select>

                            <button type="button" class="btn-secondary" style="margin-top: 8px;" onclick="crearLaboratorioRapido()">
                                + Nuevo laboratorio
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Presentación *</label>
                            <select id="rapido_presentacion_id">
                                <option value="">Seleccione...</option>
                                @foreach ($presentaciones as $presentacion)
                                    <option value="{{ $presentacion->id }}">{{ $presentacion->nombre }}</option>
                                @endforeach
                            </select>

                            <button type="button" class="btn-secondary" style="margin-top: 8px;" onclick="crearPresentacionRapida()">
                                + Nueva presentación
                            </button>
                        </div>
                    </div>

                    <div class="grid" style="grid-template-columns: repeat(4, 1fr);">
                        <div class="form-group">
                            <label>Nombre mostrado</label>
                            <input type="text" id="rapido_nombre_mostrado" placeholder="Ej: Paracetamol 500 mg tableta">
                        </div>

                        <div class="form-group">
                            <label>Unidades equivalentes *</label>
                            <input type="number" id="rapido_unidades_equivalentes" min="1" value="1">
                        </div>

                        <div class="form-group">
                            <label>Precio compra *</label>
                            <input type="number" id="rapido_precio_compra" min="0" step="0.01" value="0">
                        </div>

                        <div class="form-group">
                            <label>Precio venta *</label>
                            <input type="number" id="rapido_precio_venta" min="0" step="0.01" value="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Código de barras opcional</label>
                        <input type="text" id="rapido_codigo_barra" placeholder="Escanear o escribir código de barras">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 18px;">
                        <button type="button" class="btn-primary" onclick="guardarProductoRapido()">
                            Guardar producto rápido
                        </button>

                        <button type="button" class="btn-secondary" onclick="ocultarFormularioProductoRapido()">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
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
        mostrarFormularioProductoRapido();
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

function mostrarFormularioProductoRapido() {
    const modal = document.getElementById('modal_producto_rapido');

    if (!modal) {
        Swal.fire({
            icon: 'error',
            title: 'Modal no encontrado',
            text: 'No existe el contenedor modal_producto_rapido en la vista.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    modal.style.display = 'flex';

    setTimeout(() => {
        document.getElementById('rapido_nombre_comercial')?.focus();
    }, 100);
}

function ocultarFormularioProductoRapido() {
    document.getElementById('modal_producto_rapido').style.display = 'none';
}

async function guardarProductoRapido() {
    const datos = {
        nombre_comercial: document.getElementById('rapido_nombre_comercial').value.trim(),
        nombre_generico: document.getElementById('rapido_nombre_generico').value.trim(),
        concentracion: document.getElementById('rapido_concentracion').value.trim(),

        categoria_id: document.getElementById('rapido_categoria_id').value || null,
        laboratorio_id: document.getElementById('rapido_laboratorio_id').value || null,
        presentacion_id: document.getElementById('rapido_presentacion_id').value,

        nombre_mostrado: document.getElementById('rapido_nombre_mostrado').value.trim(),
        unidades_equivalentes: document.getElementById('rapido_unidades_equivalentes').value,

        precio_compra: document.getElementById('rapido_precio_compra').value,
        precio_venta: document.getElementById('rapido_precio_venta').value,

        codigo_barra: document.getElementById('rapido_codigo_barra').value.trim(),
    };

    if (!datos.nombre_comercial || !datos.presentacion_id || !datos.unidades_equivalentes) {
        Swal.fire({
            icon: 'warning',
            title: 'Datos incompletos',
            text: 'Debe ingresar nombre comercial, presentación y unidades equivalentes.',
            confirmButtonColor: '#6D28D9'
        });
        return;
    }

    try {
        const respuesta = await fetch(`{{ route('compras.producto-rapido') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(datos),
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
            let mensaje = 'No se pudo crear el producto.';

            if (resultado.errors) {
                mensaje = Object.values(resultado.errors).flat().join('\n');
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        /*
         * Adaptamos la respuesta del producto rápido al mismo formato
         * que ya usa seleccionarProductoCompra(producto).
         */
        const productoCreado = {
            id: resultado.producto.producto_presentacion_id,
            producto_id: resultado.producto.producto_id,
            nombre: resultado.producto.nombre_producto,
            generico: resultado.producto.nombre_generico,
            concentracion: resultado.producto.concentracion,
            presentacion: resultado.producto.presentacion,
            unidades_equivalentes: resultado.producto.unidades_equivalentes,
            precio_compra: Number(resultado.producto.precio_compra || 0),
            precio_venta: Number(resultado.producto.precio_venta || 0),
            stock_disponible: 0,
        };

        seleccionarProductoCompra(productoCreado);

        limpiarFormularioProductoRapido();
        ocultarFormularioProductoRapido();

        Swal.fire({
            icon: 'success',
            title: 'Producto creado',
            text: 'El producto fue creado y seleccionado para la compra.',
            confirmButtonColor: '#6D28D9'
        });

    } catch (error) {
        console.error('Error producto rápido:', error);

        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: error.message,
            confirmButtonColor: '#6D28D9'
        });
    }
}

function limpiarFormularioProductoRapido() {
    document.getElementById('rapido_nombre_comercial').value = '';
    document.getElementById('rapido_nombre_generico').value = '';
    document.getElementById('rapido_concentracion').value = '';
    document.getElementById('rapido_categoria_id').value = '';
    document.getElementById('rapido_laboratorio_id').value = '';
    document.getElementById('rapido_presentacion_id').value = '';
    document.getElementById('rapido_nombre_mostrado').value = '';
    document.getElementById('rapido_unidades_equivalentes').value = 1;
    document.getElementById('rapido_precio_compra').value = 0;
    document.getElementById('rapido_precio_venta').value = 0;
    document.getElementById('rapido_codigo_barra').value = '';
}
async function crearLaboratorioRapido() {
    const { value: nombre } = await Swal.fire({
        title: 'Nuevo laboratorio',
        input: 'text',
        inputLabel: 'Nombre del laboratorio',
        inputPlaceholder: 'Ej: INTI, COFAR, Bagó...',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Debe ingresar el nombre del laboratorio.';
            }
        }
    });

    if (!nombre) {
        return;
    }

    try {
        const respuesta = await fetch(`{{ route('compras.laboratorio-rapido') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                nombre: nombre.trim(),
            }),
        });

        const textoRespuesta = await respuesta.text();

        let resultado = null;

        try {
            resultado = JSON.parse(textoRespuesta);
        } catch (e) {
            console.error('Respuesta no JSON:', textoRespuesta);

            Swal.fire({
                icon: 'error',
                title: 'Respuesta inválida',
                text: 'Laravel devolvió una respuesta no válida.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        if (!respuesta.ok) {
            let mensaje = 'No se pudo crear el laboratorio.';

            if (resultado.errors) {
                mensaje = Object.values(resultado.errors).flat().join('\n');
            } else if (resultado.message) {
                mensaje = resultado.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        const selectLaboratorio = document.getElementById('rapido_laboratorio_id');

        const option = document.createElement('option');
        option.value = resultado.laboratorio.id;
        option.textContent = resultado.laboratorio.nombre;
        option.selected = true;

        selectLaboratorio.appendChild(option);

        Swal.fire({
            icon: 'success',
            title: 'Laboratorio creado',
            text: 'El laboratorio fue creado y seleccionado.',
            confirmButtonColor: '#6D28D9'
        });

    } catch (error) {
        console.error('Error laboratorio rápido:', error);

        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: error.message,
            confirmButtonColor: '#6D28D9'
        });
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('modal_producto_rapido');

        if (modal && modal.style.display === 'flex') {
            ocultarFormularioProductoRapido();
        }
    }
});

async function crearPresentacionRapida() {
    const { value: nombre } = await Swal.fire({
        title: 'Nueva presentación',
        input: 'text',
        inputLabel: 'Nombre de la presentación',
        inputPlaceholder: 'Ej: Tableta, Caja, Frasco, Ampolla, Sachet...',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Debe ingresar el nombre de la presentación.';
            }
        }
    });

    if (!nombre) {
        return;
    }

    try {
        const respuesta = await fetch(`{{ route('compras.presentacion-rapida') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                nombre: nombre.trim(),
            }),
        });

        const textoRespuesta = await respuesta.text();

        let resultado = null;

        try {
            resultado = JSON.parse(textoRespuesta);
        } catch (e) {
            console.error('Respuesta no JSON:', textoRespuesta);

            Swal.fire({
                icon: 'error',
                title: 'Respuesta inválida',
                text: 'Laravel devolvió una respuesta no válida.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        if (!respuesta.ok) {
            let mensaje = 'No se pudo crear la presentación.';

            if (resultado.errors) {
                mensaje = Object.values(resultado.errors).flat().join('\n');
            } else if (resultado.message) {
                mensaje = resultado.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        const selectPresentacion = document.getElementById('rapido_presentacion_id');

        const option = document.createElement('option');
        option.value = resultado.presentacion.id;
        option.textContent = resultado.presentacion.nombre;
        option.selected = true;

        selectPresentacion.appendChild(option);

        Swal.fire({
            icon: 'success',
            title: 'Presentación creada',
            text: 'La presentación fue creada y seleccionada.',
            confirmButtonColor: '#6D28D9'
        });

    } catch (error) {
        console.error('Error presentación rápida:', error);

        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: error.message,
            confirmButtonColor: '#6D28D9'
        });
    }
}

async function crearCategoriaRapida() {
    const { value: nombre } = await Swal.fire({
        title: 'Nueva categoría',
        input: 'text',
        inputLabel: 'Nombre de la categoría',
        inputPlaceholder: 'Ej: Analgésicos, Antibióticos, Jarabes...',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Debe ingresar el nombre de la categoría.';
            }
        }
    });

    if (!nombre) {
        return;
    }

    try {
        const respuesta = await fetch(`{{ route('compras.categoria-rapida') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                nombre: nombre.trim(),
            }),
        });

        const textoRespuesta = await respuesta.text();

        let resultado = null;

        try {
            resultado = JSON.parse(textoRespuesta);
        } catch (e) {
            console.error('Respuesta no JSON:', textoRespuesta);

            Swal.fire({
                icon: 'error',
                title: 'Respuesta inválida',
                text: 'Laravel devolvió una respuesta no válida.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        if (!respuesta.ok) {
            let mensaje = 'No se pudo crear la categoría.';

            if (resultado.errors) {
                mensaje = Object.values(resultado.errors).flat().join('\n');
            } else if (resultado.message) {
                mensaje = resultado.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        const selectCategoria = document.getElementById('rapido_categoria_id');

        const option = document.createElement('option');
        option.value = resultado.categoria.id;
        option.textContent = resultado.categoria.nombre;
        option.selected = true;

        selectCategoria.appendChild(option);

        Swal.fire({
            icon: 'success',
            title: 'Categoría creada',
            text: 'La categoría fue creada y seleccionada.',
            confirmButtonColor: '#6D28D9'
        });

    } catch (error) {
        console.error('Error categoría rápida:', error);

        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: error.message,
            confirmButtonColor: '#6D28D9'
        });
    }
}

async function crearProveedorRapido() {
    const { value: formValues } = await Swal.fire({
        title: 'Nuevo proveedor',
        width: 620,
        html: `
            <div class="swal-form-grid">
                <div class="swal-form-group swal-form-full">
                    <label>Nombre del proveedor *</label>
                    <input id="swal_proveedor_nombre" class="swal-input-custom" placeholder="Ej: Distribuidora Farma">
                </div>

                <div class="swal-form-group">
                    <label>Teléfono</label>
                    <input id="swal_proveedor_telefono" class="swal-input-custom" placeholder="Ej: 76543210">
                </div>

                <div class="swal-form-group">
                    <label>Contacto</label>
                    <input id="swal_proveedor_contacto" class="swal-input-custom" placeholder="Ej: Juan Pérez">
                </div>

                <div class="swal-form-group swal-form-full">
                    <label>Dirección</label>
                    <input id="swal_proveedor_direccion" class="swal-input-custom" placeholder="Ej: Av. Principal #123">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6D28D9',
        focusConfirm: false,
        didOpen: () => {
            document.getElementById('swal_proveedor_nombre').focus();
        },
        preConfirm: () => {
            const nombre = document.getElementById('swal_proveedor_nombre').value.trim();

            if (!nombre) {
                Swal.showValidationMessage('Debe ingresar el nombre del proveedor.');
                return false;
            }

            return {
                nombre: nombre,
                telefono: document.getElementById('swal_proveedor_telefono').value.trim(),
                contacto: document.getElementById('swal_proveedor_contacto').value.trim(),
                direccion: document.getElementById('swal_proveedor_direccion').value.trim(),
            };
        }
    });

    if (!formValues) {
        return;
    }

    try {
        const respuesta = await fetch(`{{ route('compras.proveedor-rapido') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(formValues),
        });

        const textoRespuesta = await respuesta.text();

        let resultado = null;

        try {
            resultado = JSON.parse(textoRespuesta);
        } catch (e) {
            console.error('Respuesta no JSON:', textoRespuesta);

            Swal.fire({
                icon: 'error',
                title: 'Respuesta inválida',
                text: 'Laravel devolvió una respuesta no válida.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        if (!respuesta.ok) {
            let mensaje = 'No se pudo crear el proveedor.';

            if (resultado.errors) {
                mensaje = Object.values(resultado.errors).flat().join('\n');
            } else if (resultado.message) {
                mensaje = resultado.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        const selectProveedor = document.getElementById('proveedor_id');

        const option = document.createElement('option');
        option.value = resultado.proveedor.id;
        option.textContent = resultado.proveedor.nombre;
        option.selected = true;

        selectProveedor.appendChild(option);

        Swal.fire({
            icon: 'success',
            title: 'Proveedor creado',
            text: 'El proveedor fue creado y seleccionado.',
            confirmButtonColor: '#6D28D9'
        });

    } catch (error) {
        console.error('Error proveedor rápido:', error);

        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: error.message,
            confirmButtonColor: '#6D28D9'
        });
    }
}
</script>

@endsection