@extends('layouts.app')

@section('title', 'Registrar baja de inventario | Santo Remedio')
@section('page-title', 'Registrar baja de inventario')
@section('page-subtitle', 'Retirar productos del inventario por vencimiento, daño, pérdida u otros motivos')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Registrar baja de inventario</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Seleccione un producto con stock disponible y registre la cantidad que será retirada.
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

    <form method="POST" action="{{ route('bajas-inventario.store') }}" id="form_baja">
        @csrf

        <div class="card" style="background: #FAFAFA; margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Producto seleccionado</h3>

            <div class="form-group">
                <label>Buscar producto en inventario *</label>
                <input
                    type="text"
                    id="buscador_producto_baja"
                    placeholder="Buscar por nombre, genérico, concentración o laboratorio"
                    autocomplete="off"
                >
            </div>

            <div id="resultados_producto_baja" class="card" style="display:none; margin-top:12px; background:#FFFFFF;"></div>

            <div id="producto_seleccionado" style="{{ $inventarioSeleccionado ? '' : 'display:none;' }} margin-top: 18px; padding: 16px; border: 1px solid #DDD6FE; border-radius: 12px; background: #F5F3FF;">
                <h4 style="margin: 0 0 8px; color: #4C1D95;">Producto para baja</h4>

                <p style="margin: 0; color: #374151;">
                    <strong id="producto_nombre">
                        {{ $inventarioSeleccionado->producto->nombre_comercial ?? '' }}
                    </strong>
                    <br>

                    <span id="producto_detalle" style="color: #6B7280;">
                        @if ($inventarioSeleccionado)
                            {{ collect([
                                $inventarioSeleccionado->producto->nombre_generico ?? null,
                                $inventarioSeleccionado->producto->concentracion ?? null,
                                $inventarioSeleccionado->producto->laboratorio->nombre ?? null,
                            ])->filter()->join(' | ') }}
                        @endif
                    </span>
                    <br>

                    <span id="producto_lote" style="color: #4C1D95;">
                        @if ($inventarioSeleccionado)
                            Lote: {{ $inventarioSeleccionado->lote->numero_lote ?? 'Sin lote' }}

                            @if ($inventarioSeleccionado->lote?->fecha_vencimiento)
                                | Vence: {{ $inventarioSeleccionado->lote->fecha_vencimiento->format('d/m/Y') }}
                            @endif
                        @endif
                    </span>
                    <br>

                    <span id="producto_stock" style="color: #991B1B; font-weight: bold;">
                        @if ($inventarioSeleccionado)
                            Stock disponible: {{ $inventarioSeleccionado->stock_actual }}
                        @endif
                    </span>
                </p>
            </div>

            <input
                type="hidden"
                name="inventario_id"
                id="inventario_id"
                value="{{ old('inventario_id', $inventarioSeleccionado->id ?? '') }}"
            >
        </div>

        <div class="card" style="background: #FAFAFA; margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Datos de la baja</h3>

            <div class="form-grid">
                <div class="form-group">
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

                <div class="form-group">
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
                    <small style="color: #6B7280;">
                        No puede superar el stock disponible del producto seleccionado.
                    </small>
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px;">
                <label>Observación</label>
                <textarea
                    name="observacion"
                    rows="4"
                    placeholder="Ej. Producto vencido retirado de estantería, envase dañado, pérdida confirmada, ajuste autorizado por administración..."
                >{{ old('observacion') }}</textarea>
            </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="submit" class="btn-primary">
                Registrar baja
            </button>

            <a href="{{ route('bajas-inventario.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

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

                <button type="button" class="btn-primary" data-producto="${dataProducto}" onclick="seleccionarProductoBaja(this)">
                    Seleccionar
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