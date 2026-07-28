@extends('layouts.app')

@section('title', 'Registrar baja de inventario | Santo Remedio')
@section('page-title', 'Registrar baja de inventario')
@section('page-subtitle', 'Retirar productos del inventario por vencimiento, daño, pérdida u otros motivos')

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

<form method="POST" action="{{ route('bajas-inventario.store') }}" id="form_baja" class="stock-out-form">
    @csrf

    <div class="stock-out-layout">

        <section class="stock-out-main">

            <div class="stock-out-header-card">
                <div>
                    <h2>
                        <i class="bi bi-box-arrow-down"></i>
                        Registrar baja de inventario
                    </h2>

                    <p>
                        Seleccione un producto con stock disponible y registre la cantidad que será retirada.
                    </p>
                </div>
            </div>

            <div class="stock-out-card">
                <div class="stock-out-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-search"></i>
                            Buscar producto
                        </h3>
                        <small>Busque por nombre, genérico, concentración o laboratorio</small>
                    </div>
                </div>

                <div class="form-group stock-out-search-group">
                    <label>Buscar producto en inventario *</label>

                    <div class="stock-out-search-box">
                        <i class="bi bi-upc-scan"></i>
                        <input
                            type="text"
                            id="buscador_producto_baja"
                            placeholder="Buscar producto con stock disponible"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div id="resultados_producto_baja" class="stock-out-results" style="display:none;"></div>
            </div>

            <div
                id="producto_seleccionado"
                class="stock-out-selected-card"
                style="{{ $inventarioSeleccionado ? '' : 'display:none;' }}"
            >
                <div class="stock-out-selected-icon">
                    <i class="bi bi-capsule"></i>
                </div>

                <div class="stock-out-selected-info">
                    <span>Producto para baja</span>

                    <strong id="producto_nombre">
                        {{ $inventarioSeleccionado->producto->nombre_comercial ?? '' }}
                    </strong>

                    <small id="producto_detalle">
                        @if ($inventarioSeleccionado)
                            {{ collect([
                                $inventarioSeleccionado->producto->nombre_generico ?? null,
                                $inventarioSeleccionado->producto->concentracion ?? null,
                                $inventarioSeleccionado->producto->laboratorio->nombre ?? null,
                            ])->filter()->join(' | ') }}
                        @endif
                    </small>

                    <small id="producto_lote" class="stock-out-lot">
                        @if ($inventarioSeleccionado)
                            Lote: {{ $inventarioSeleccionado->lote->numero_lote ?? 'Sin lote' }}

                            @if ($inventarioSeleccionado->lote?->fecha_vencimiento)
                                | Vence: {{ $inventarioSeleccionado->lote->fecha_vencimiento->format('d/m/Y') }}
                            @endif
                        @endif
                    </small>

                    <small id="producto_stock" class="stock-out-stock">
                        @if ($inventarioSeleccionado)
                            Stock disponible: {{ $inventarioSeleccionado->stock_actual }}
                        @endif
                    </small>
                </div>
            </div>

            <input
                type="hidden"
                name="inventario_id"
                id="inventario_id"
                value="{{ old('inventario_id', $inventarioSeleccionado->id ?? '') }}"
            >

        </section>

        <aside class="stock-out-summary">

            <div class="stock-out-summary-card">
                <h3>
                    <i class="bi bi-clipboard-x"></i>
                    Datos de la baja
                </h3>

                <div class="stock-out-side-grid">
                    <div class="form-group stock-out-full">
                        <label>Motivo *</label>
                        <select name="motivo" required>
                            <option value="">Seleccione un motivo</option>
                            <option value="vencimiento" @selected(old('motivo') === 'vencimiento')>Vencimiento</option>
                            <option value="danado" @selected(old('motivo') === 'danado')>Dañado</option>
                            <option value="perdido" @selected(old('motivo') === 'perdido')>Perdido</option>
                            <option value="ajuste_autorizado" @selected(old('motivo') === 'ajuste_autorizado')>Ajuste autorizado</option>
                            <option value="otro" @selected(old('motivo') === 'otro')>Otro</option>
                        </select>
                    </div>

                    <div class="form-group stock-out-full">
                        <label>Cantidad a dar de baja *</label>
                        <input
                            type="number"
                            name="cantidad"
                            id="cantidad_baja"
                            min="1"
                            max="{{ $inventarioSeleccionado->stock_actual ?? '' }}"
                            value="{{ old('cantidad', $inventarioSeleccionado->stock_actual ?? 1) }}"
                            required
                        >
                    </div>
                </div>

                <div class="stock-out-help-card">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>
                        La cantidad no puede superar el stock disponible del producto seleccionado.
                    </span>
                </div>
            </div>

            <div class="stock-out-summary-card">
                <h3>
                    <i class="bi bi-card-text"></i>
                    Observación
                </h3>

                <div class="form-group">
                    <label>Detalle de la baja</label>
                    <textarea
                        name="observacion"
                        rows="5"
                        placeholder="Ej. Producto vencido retirado de estantería, envase dañado, pérdida confirmada..."
                    >{{ old('observacion') }}</textarea>
                </div>
            </div>

            <div class="stock-out-info-card">
                <div class="stock-out-info-icon">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>
                    <strong>Control de inventario</strong>
                    <span>Esta acción retirará stock real y quedará registrada en movimientos.</span>
                </div>
            </div>

            <div class="stock-out-actions">
                <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn-primary">
                    <i class="bi bi-check2-circle"></i>
                    Registrar baja
                </button>
            </div>

        </aside>

    </div>
</form>

<script>
const buscarBajaProductosUrl = "{{ route('bajas-inventario.buscar-productos') }}";

const buscadorBaja = document.getElementById('buscador_producto_baja');
const resultadosBaja = document.getElementById('resultados_producto_baja');

const productoSeleccionado = document.getElementById('producto_seleccionado');
const productoNombre = document.getElementById('producto_nombre');
const productoDetalle = document.getElementById('producto_detalle');
const productoLote = document.getElementById('producto_lote');
const productoStock = document.getElementById('producto_stock');

const inventarioId = document.getElementById('inventario_id');
const cantidadBaja = document.getElementById('cantidad_baja');

let stockDisponibleSeleccionado = Number("{{ $inventarioSeleccionado->stock_actual ?? 0 }}");
let timeoutBaja = null;

if (stockDisponibleSeleccionado > 0) {
    cantidadBaja.max = stockDisponibleSeleccionado;

    if (!cantidadBaja.value || Number(cantidadBaja.value) < 1) {
        cantidadBaja.value = stockDisponibleSeleccionado;
    }

    if (Number(cantidadBaja.value) > stockDisponibleSeleccionado) {
        cantidadBaja.value = stockDisponibleSeleccionado;
    }
}

buscadorBaja.addEventListener('input', function () {
    clearTimeout(timeoutBaja);

    const termino = this.value.trim();

    if (termino.length < 2) {
        resultadosBaja.style.display = 'none';
        resultadosBaja.innerHTML = '';
        return;
    }

    timeoutBaja = setTimeout(() => {
        buscarProductosBaja(termino);
    }, 250);
});

async function buscarProductosBaja(termino) {
    try {
        const response = await fetch(`${buscarBajaProductosUrl}?busqueda=${encodeURIComponent(termino)}`);
        const productos = await response.json();

        mostrarResultadosBaja(productos);
    } catch (error) {
        resultadosBaja.style.display = 'block';
        resultadosBaja.innerHTML = '<p style="color:#991B1B;">Error al buscar productos.</p>';
    }
}

function mostrarResultadosBaja(productos) {
    resultadosBaja.style.display = 'block';

    if (!Array.isArray(productos) || productos.length === 0) {
        resultadosBaja.innerHTML = '<p style="color:#6B7280;">No se encontraron productos con stock disponible.</p>';
        return;
    }

    resultadosBaja.innerHTML = productos.map(producto => {
        const dataProducto = encodeURIComponent(JSON.stringify(producto));

        const detalle = [
            producto.generico,
            producto.concentracion,
            producto.laboratorio
        ].filter(Boolean).join(' | ');

        const vencimiento = producto.fecha_vencimiento
            ? ` | Vence: ${producto.fecha_vencimiento}`
            : '';

        return `
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #E5E7EB;">
                <div>
                    <strong>${producto.producto}</strong>

                    <div style="color:#6B7280; font-size:13px; margin-top:3px;">
                        ${detalle || '-'}
                    </div>

                    <div style="color:#4C1D95; font-size:13px; margin-top:3px;">
                        Lote: ${producto.lote}${vencimiento}
                    </div>

                    <div style="color:#991B1B; font-size:13px; margin-top:3px;">
                        Stock disponible: ${producto.stock_actual}
                    </div>
                </div>

                <button type="button" class="icon-action icon-action-primary" data-producto="${dataProducto}" onclick="seleccionarProductoBaja(this)" title="Seleccionar">
                    <i class="bi bi-check2"></i>
                </button>
            </div>
        `;
    }).join('');
}

function seleccionarProductoBaja(boton) {
    try {
        const producto = JSON.parse(decodeURIComponent(boton.dataset.producto));

        inventarioId.value = producto.inventario_id;
        stockDisponibleSeleccionado = Number(producto.stock_actual || 0);

        productoNombre.textContent = producto.producto;

        productoDetalle.textContent = [
            producto.generico,
            producto.concentracion,
            producto.laboratorio
        ].filter(Boolean).join(' | ') || '-';

        productoLote.textContent = producto.fecha_vencimiento
            ? `Lote: ${producto.lote} | Vence: ${producto.fecha_vencimiento}`
            : `Lote: ${producto.lote}`;

        productoStock.textContent = `Stock disponible: ${stockDisponibleSeleccionado}`;

        cantidadBaja.max = stockDisponibleSeleccionado;

        cantidadBaja.value = stockDisponibleSeleccionado;

        productoSeleccionado.style.display = 'block';

        buscadorBaja.value = '';
        resultadosBaja.style.display = 'none';
        resultadosBaja.innerHTML = '';
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo seleccionar el producto.',
            confirmButtonColor: '#6D28D9'
        });
    }
}

document.getElementById('form_baja').addEventListener('submit', function (event) {
    if (!inventarioId.value) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Producto no seleccionado',
            text: 'Debe seleccionar un producto del inventario.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    const cantidad = Number(cantidadBaja.value || 0);

    if (cantidad < 1) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Cantidad inválida',
            text: 'La cantidad debe ser mayor a cero.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (stockDisponibleSeleccionado > 0 && cantidad > stockDisponibleSeleccionado) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Stock insuficiente',
            text: `Solo hay ${stockDisponibleSeleccionado} unidad(es) disponibles.`,
            confirmButtonColor: '#6D28D9'
        });
    }
});
</script>

@endsection