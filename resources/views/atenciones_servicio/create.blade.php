@extends('layouts.app')

@section('title', 'Registrar atención de servicio | Santo Remedio')
@section('page-title', 'Registrar atención de servicio')
@section('page-subtitle', 'Cobro de servicios realizados por la farmacia')

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

<form method="POST" action="{{ route('atenciones-servicio.store') }}" id="form_atencion_servicio" class="service-attention-form">
    @csrf

    <div class="service-attention-layout">

        <section class="service-attention-main">

            <div class="service-attention-header-card">
                <div>
                    <h2>
                        <i class="bi bi-heart-pulse"></i>
                        Registrar atención de servicio
                    </h2>

                    <p>
                        Sucursal: <strong>{{ $sucursal->nombre }}</strong> ·
                        Caja: <strong>{{ $cajaAbierta->turno->nombre ?? 'Sin turno' }}</strong> ·
                        Apertura: <strong>{{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</strong>
                    </p>
                </div>
            </div>

            <div class="service-attention-card">
                <div class="service-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-clipboard2-pulse"></i>
                            Datos del servicio
                        </h3>
                        <small>Seleccione el servicio, cliente y cantidad</small>
                    </div>
                </div>

                <div class="service-attention-grid">
                    <div class="form-group service-full">
                        <label>Servicio *</label>
                        <select name="servicio_farmacia_id" id="servicio_farmacia_id" required>
                            <option value="">Seleccione servicio...</option>

                            @foreach ($servicios as $servicio)
                                @php
                                    $insumosServicio = $servicio->insumos->map(function ($insumo) {
                                        return [
                                            "id" => $insumo->id,
                                            "nombre" => $insumo->productoPresentacion->nombre_mostrado
                                                ?? $insumo->producto->nombre_comercial
                                                ?? "Insumo",
                                            "cantidad" => $insumo->cantidad,
                                            "unidades_necesarias" => $insumo->unidades_necesarias,
                                            "presentacion" => $insumo->productoPresentacion->presentacion->nombre ?? "",
                                            "stock_disponible" => $insumo->stock_disponible ?? 0,
                                            "stock_aproximado_presentacion" => $insumo->stock_aproximado_presentacion ?? 0,
                                        ];
                                    })->values();
                                @endphp

                                <option
                                    value="{{ $servicio->id }}"
                                    data-precio="{{ $servicio->precio }}"
                                    data-insumos='@json($insumosServicio)'
                                    @selected(old('servicio_farmacia_id') == $servicio->id)
                                >
                                    {{ $servicio->nombre }} - {{ number_format($servicio->precio, 2) }} Bs
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group service-full">
                        <label>Cliente</label>
                        <select name="cliente_id">
                            <option value="">Consumidor final</option>

                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                    {{ $cliente->nombre }}
                                    @if ($cliente->ci_nit)
                                        - CI/NIT: {{ $cliente->ci_nit }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cantidad *</label>
                        <input
                            type="number"
                            name="cantidad"
                            id="cantidad"
                            min="1"
                            value="{{ old('cantidad', 1) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Precio unitario</label>
                        <input
                            type="number"
                            id="precio_unitario"
                            step="0.01"
                            min="0"
                            value="0"
                            disabled
                        >
                    </div>

                    <div class="form-group">
                        <label>Descuento Bs</label>
                        <input
                            type="number"
                            name="descuento"
                            id="descuento"
                            step="0.01"
                            min="0"
                            value="{{ old('descuento', 0) }}"
                        >
                    </div>
                </div>
            </div>

            <div class="service-attention-card">
                <div class="service-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-box-seam"></i>
                            Insumos que se descontarán
                        </h3>
                        <small>El sistema usará FEFO y evitará lotes vencidos</small>
                    </div>
                </div>

                <div class="table-container service-table-container">
                    <table class="table service-table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Stock</th>
                                <th>Cantidad base</th>
                                <th>Descuento total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody id="tabla_insumos_preview">
                            <tr>
                                <td colspan="6" class="service-empty">
                                    Seleccione un servicio para ver insumos.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <aside class="service-attention-summary">

            <div class="service-summary-card">
                <h3>
                    <i class="bi bi-cash-coin"></i>
                    Cobro
                </h3>

                <div class="form-group">
                    <label>Método de pago *</label>
                    <select name="metodo_pago_id" id="metodo_pago_id" required>
                        <option value="">Seleccione método...</option>

                        @foreach ($metodosPago as $metodo)
                            <option value="{{ $metodo->id }}" @selected(old('metodo_pago_id') == $metodo->id)>
                                {{ $metodo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="service-totals">
                    <div class="service-total-item">
                        <span>Subtotal</span>
                        <strong id="subtotal_text">0.00 Bs</strong>
                    </div>

                    <div class="service-total-item">
                        <span>Descuento</span>
                        <strong id="descuento_text">0.00 Bs</strong>
                    </div>

                    <div class="service-total-item service-total-main">
                        <span>Total a cobrar</span>
                        <strong id="total_text">0.00 Bs</strong>
                    </div>

                    <div class="service-total-item">
                        <span>Insumos</span>
                        <strong id="insumos_count_text">0</strong>
                    </div>
                </div>
            </div>

            <div class="service-summary-card">
                <div class="form-group">
                    <label>Observación</label>
                    <textarea
                        name="observacion"
                        rows="3"
                        placeholder="Opcional"
                    >{{ old('observacion') }}</textarea>
                </div>
            </div>

            <input type="hidden" name="insumos_filtrados" value="1">
            <div id="inputs_insumos_usados"></div>

            <div class="service-actions">
                <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn-primary">
                    <i class="bi bi-check2-circle"></i>
                    Registrar atención
                </button>
            </div>

        </aside>

    </div>
</form>

<script>
const servicioSelect = document.getElementById('servicio_farmacia_id');
const cantidadInput = document.getElementById('cantidad');
const descuentoInput = document.getElementById('descuento');

const precioUnitarioInput = document.getElementById('precio_unitario');
const subtotalText = document.getElementById('subtotal_text');
const descuentoText = document.getElementById('descuento_text');
const totalText = document.getElementById('total_text');
const insumosCountText = document.getElementById('insumos_count_text');
const tablaInsumosPreview = document.getElementById('tabla_insumos_preview');
const inputsInsumosUsados = document.getElementById('inputs_insumos_usados');

let insumosSeleccionados = [];
let servicioActualId = null;

function obtenerServicioSeleccionado() {
    const option = servicioSelect.options[servicioSelect.selectedIndex];

    if (!option || !option.value) {
        return {
            precio: 0,
            insumos: []
        };
    }

    let insumos = [];

    try {
        insumos = JSON.parse(option.dataset.insumos || '[]');
    } catch (error) {
        insumos = [];
    }

    return {
        precio: Number(option.dataset.precio || 0),
        insumos: insumos
    };
}

function actualizarResumenServicio(resetearInsumos = false) {
    const servicio = obtenerServicioSeleccionado();

    if (resetearInsumos || servicioActualId !== servicioSelect.value) {
        servicioActualId = servicioSelect.value;
        insumosSeleccionados = servicio.insumos || [];
    }

    let cantidad = parseInt(cantidadInput.value || 1);

    if (cantidad < 1) {
        cantidad = 1;
        cantidadInput.value = 1;
    }

    let descuento = Number(descuentoInput.value || 0);

    if (descuento < 0) {
        descuento = 0;
        descuentoInput.value = 0;
    }

    const subtotal = servicio.precio * cantidad;

    if (descuento > subtotal) {
        descuento = subtotal;
        descuentoInput.value = subtotal.toFixed(2);
    }

    const total = subtotal - descuento;

    precioUnitarioInput.value = servicio.precio.toFixed(2);
    subtotalText.textContent = subtotal.toFixed(2) + ' Bs';
    descuentoText.textContent = descuento.toFixed(2) + ' Bs';
    totalText.textContent = total.toFixed(2) + ' Bs';
    insumosCountText.textContent = insumosSeleccionados.length + ' seleccionado(s)';

    renderInsumosPreview(insumosSeleccionados, cantidad);
}

function renderInsumosPreview(insumos, cantidad) {
    tablaInsumosPreview.innerHTML = '';

    if (inputsInsumosUsados) {
        inputsInsumosUsados.innerHTML = '';
    }

    if (!insumos || insumos.length === 0) {
        tablaInsumosPreview.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; color:#6B7280;">
                    No se descontará ningún insumo en esta atención.
                </td>
            </tr>
        `;
        return;
    }

    insumos.forEach((insumo, index) => {
        const totalUnidades = Number(insumo.unidades_necesarias || 0) * cantidad;
        const stockDisponible = Number(insumo.stock_disponible || 0);
        const stockAproximado = Number(insumo.stock_aproximado_presentacion || 0);
        const stockInsuficiente = totalUnidades > stockDisponible;

        tablaInsumosPreview.innerHTML += `
            <tr>
                <td>
                    <strong>${insumo.nombre}</strong>
                </td>

                <td>
                    ${stockDisponible} unidad(es)
                    <br>
                    <small style="color:#6B7280;">
                        ${insumo.presentacion || '-'} · Aprox: ${stockAproximado}
                    </small>

                    ${stockInsuficiente ? `
                        <br>
                        <small style="color:#991B1B; font-weight:bold;">
                            Stock insuficiente
                        </small>
                    ` : ''}
                </td>

                <td>${insumo.cantidad}</td>

                <td>
                    ${totalUnidades}
                    <br>
                    <small style="color:#6B7280;">
                        por ${cantidad} atención(es)
                    </small>
                </td>

                <td>
                    <button type="button" class="icon-action icon-action-danger" onclick="quitarInsumoAtencion(${index})" title="Quitar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;

        if (inputsInsumosUsados) {
            inputsInsumosUsados.innerHTML += `
                <input type="hidden" name="insumos_usados[]" value="${insumo.id}">
            `;
        }
    });
}

function quitarInsumoAtencion(index) {
    insumosSeleccionados.splice(index, 1);
    actualizarResumenServicio(false);
}
document.getElementById('form_atencion_servicio').addEventListener('submit', function (event) {
    if (!servicioSelect.value) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Servicio requerido',
            text: 'Debe seleccionar un servicio.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (!document.getElementById('metodo_pago_id').value) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Método de pago requerido',
            text: 'Debe seleccionar el método de pago.',
            confirmButtonColor: '#6D28D9'
        });
    }
});

servicioSelect.addEventListener('change', function () {
    actualizarResumenServicio(true);
});

cantidadInput.addEventListener('input', function () {
    actualizarResumenServicio(false);
});

descuentoInput.addEventListener('input', function () {
    actualizarResumenServicio(false);
});

actualizarResumenServicio(true);
</script>

@endsection