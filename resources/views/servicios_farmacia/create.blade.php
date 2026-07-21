@extends('layouts.app')

@section('title', 'Nuevo servicio de farmacia | Santo Remedio')
@section('page-title', 'Nuevo servicio de farmacia')
@section('page-subtitle', 'Crear servicio y configurar insumos opcionales')

@section('content')

<div class="card">

    <div style="margin-bottom: 22px;">
        <h2 style="margin: 0; color: #4C1D95;">Nuevo servicio de farmacia</h2>
        <p style="margin: 6px 0 0; color: #6B7280;">
            Configure servicios como inyectables, controles, curaciones, nebulizaciones u otros.
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

    <form method="POST" action="{{ route('servicios-farmacia.store') }}" id="form_servicio">
        @csrf

        <div class="card" style="background: #FAFAFA; margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Datos del servicio</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre del servicio *</label>
                    <input
                        type="text"
                        name="nombre"
                        value="{{ old('nombre') }}"
                        placeholder="Ej: Aplicación de inyectable"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Tipo *</label>
                    <select name="tipo" required>
                        <option value="">Seleccione...</option>
                        <option value="inyectable" @selected(old('tipo') === 'inyectable')>Inyectable</option>
                        <option value="control" @selected(old('tipo') === 'control')>Control</option>
                        <option value="curacion" @selected(old('tipo') === 'curacion')>Curación</option>
                        <option value="nebulizacion" @selected(old('tipo') === 'nebulizacion')>Nebulización</option>
                        <option value="orientacion" @selected(old('tipo') === 'orientacion')>Orientación</option>
                        <option value="otro" @selected(old('tipo', 'otro') === 'otro')>Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Precio del servicio *</label>
                    <input
                        type="number"
                        name="precio"
                        step="0.01"
                        min="0"
                        value="{{ old('precio', 0) }}"
                        required
                    >
                </div>
            </div>

            <div class="form-group" style="margin-top: 14px;">
                <label>Descripción</label>
                <textarea
                    name="descripcion"
                    rows="3"
                    placeholder="Opcional"
                >{{ old('descripcion') }}</textarea>
            </div>
        </div>

        <div class="card" style="background: #FAFAFA; margin-bottom: 22px;">
            <h3 style="margin-top: 0; color: #4C1D95;">Insumos del servicio</h3>

            <p style="color:#6B7280; margin-top:0;">
                Opcional. Agregue productos que se descontarán automáticamente cuando se registre una atención.
            </p>

            <div class="form-group">
                <label>Buscar producto/insumo</label>
                <input
                    type="text"
                    id="buscador_insumo_servicio"
                    placeholder="Buscar por producto, presentación, laboratorio..."
                    autocomplete="off"
                >
            </div>

            <div id="resultados_insumos_servicio" class="card" style="display:none; margin-top:12px; background:#FFFFFF;"></div>

            <div class="table-container" style="margin-top: 18px;">
                <table class="table" id="tabla_insumos_servicio">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Presentación</th>
                            <th>Cantidad</th>
                            <th>Descuenta del inventario</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="insumos_vacios">
                            <td colspan="5" style="text-align:center; color:#6B7280;">
                                No hay insumos agregados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="inputs_insumos_servicio"></div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="submit" class="btn-primary">
                Guardar servicio
            </button>

            <a href="{{ route('servicios-farmacia.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

@php
    $insumosAntiguosServicio = collect(old('insumos', []))->map(function ($item) {
        return [
            'producto_id' => $item['producto_id'] ?? null,
            'producto_presentacion_id' => $item['producto_presentacion_id'] ?? null,
            'nombre_mostrado' => $item['nombre_mostrado'] ?? 'Insumo seleccionado',
            'laboratorio' => $item['laboratorio'] ?? '',
            'concentracion' => $item['concentracion'] ?? '',
            'presentacion' => $item['presentacion'] ?? '',
            'unidades_equivalentes' => max((int) ($item['unidades_equivalentes'] ?? 1), 1),
            'cantidad' => max((int) ($item['cantidad'] ?? 1), 1),
        ];
    })->filter(function ($item) {
        return !empty($item['producto_presentacion_id']);
    })->values();
@endphp

<script>
const buscarInsumosServicioUrl = "{{ route('servicios-farmacia.buscar-productos') }}";

const buscadorInsumoServicio = document.getElementById('buscador_insumo_servicio');
const resultadosInsumosServicio = document.getElementById('resultados_insumos_servicio');
const tablaInsumosServicio = document.querySelector('#tabla_insumos_servicio tbody');
const insumosVacios = document.getElementById('insumos_vacios');
const inputsInsumosServicio = document.getElementById('inputs_insumos_servicio');

let insumosAntiguosServicio = @json($insumosAntiguosServicio);
let insumosServicio = insumosAntiguosServicio.length ? insumosAntiguosServicio : [];

let timeoutInsumoServicio = null;

buscadorInsumoServicio.addEventListener('input', function () {
    clearTimeout(timeoutInsumoServicio);

    const termino = this.value.trim();

    if (termino.length < 2) {
        resultadosInsumosServicio.style.display = 'none';
        resultadosInsumosServicio.innerHTML = '';
        return;
    }

    timeoutInsumoServicio = setTimeout(() => {
        buscarInsumosServicio(termino);
    }, 250);
});

async function buscarInsumosServicio(termino) {
    try {
        const response = await fetch(`${buscarInsumosServicioUrl}?busqueda=${encodeURIComponent(termino)}`);
        const productos = await response.json();

        mostrarResultadosInsumosServicio(productos);
    } catch (error) {
        resultadosInsumosServicio.style.display = 'block';
        resultadosInsumosServicio.innerHTML = '<p style="color:#991B1B;">Error al buscar insumos.</p>';
    }
}

function mostrarResultadosInsumosServicio(productos) {
    resultadosInsumosServicio.style.display = 'block';

    if (!Array.isArray(productos) || productos.length === 0) {
        resultadosInsumosServicio.innerHTML = '<p style="color:#6B7280;">No se encontraron productos.</p>';
        return;
    }

    resultadosInsumosServicio.innerHTML = productos.map(producto => {
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
                        Cada cantidad descuenta ${unidades} unidad(es) del inventario.
                    </div>
                </div>

                <button type="button" class="btn-primary" data-producto="${dataProducto}" onclick="agregarInsumoServicioDesdeBoton(this)">
                    Agregar
                </button>
            </div>
        `;
    }).join('');
}

function agregarInsumoServicioDesdeBoton(boton) {
    try {
        const producto = JSON.parse(decodeURIComponent(boton.dataset.producto));
        agregarInsumoServicio(producto);
    } catch (error) {
        console.error('Error al seleccionar insumo:', error);

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo seleccionar el insumo.',
            confirmButtonColor: '#6D28D9'
        });
    }
}

function agregarInsumoServicio(producto) {
    const existe = insumosServicio.find(item => String(item.producto_presentacion_id) === String(producto.id));

    if (existe) {
        existe.cantidad += 1;
    } else {
        insumosServicio.push({
            producto_id: Number(producto.producto_id),
            producto_presentacion_id: Number(producto.id),
            nombre_mostrado: producto.nombre_mostrado || producto.nombre,
            laboratorio: producto.laboratorio || '',
            concentracion: producto.concentracion || '',
            presentacion: producto.presentacion || '',
            unidades_equivalentes: Number(producto.unidades_equivalentes || 1),
            cantidad: 1
        });
    }

    buscadorInsumoServicio.value = '';
    resultadosInsumosServicio.style.display = 'none';
    resultadosInsumosServicio.innerHTML = '';

    renderInsumosServicio();
}

function cambiarCantidadInsumoServicio(index, cantidad) {
    cantidad = parseInt(cantidad || 1);

    if (cantidad < 1) {
        cantidad = 1;
    }

    insumosServicio[index].cantidad = cantidad;
    renderInsumosServicio();
}

function quitarInsumoServicio(index) {
    insumosServicio.splice(index, 1);
    renderInsumosServicio();
}

function renderInsumosServicio() {
    tablaInsumosServicio.innerHTML = '';
    inputsInsumosServicio.innerHTML = '';

    if (insumosServicio.length === 0) {
        tablaInsumosServicio.appendChild(insumosVacios);
        insumosVacios.style.display = '';
        return;
    }

    insumosVacios.style.display = 'none';

    insumosServicio.forEach((item, index) => {
        const unidadesNecesarias = item.cantidad * item.unidades_equivalentes;

        tablaInsumosServicio.innerHTML += `
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

                <td>
                    <input
                        type="number"
                        min="1"
                        value="${item.cantidad}"
                        style="width:80px;"
                        onchange="cambiarCantidadInsumoServicio(${index}, this.value)"
                    >
                </td>

                <td>
                    ${unidadesNecesarias}
                    <br>
                    <small style="color:#6B7280;">
                        ${item.cantidad} x ${item.unidades_equivalentes}
                    </small>
                </td>

                <td>
                    <button type="button" class="btn-danger" onclick="quitarInsumoServicio(${index})">
                        Quitar
                    </button>
                </td>
            </tr>
        `;

        inputsInsumosServicio.innerHTML += `
            <input type="hidden" name="insumos[${index}][producto_id]" value="${item.producto_id}">
            <input type="hidden" name="insumos[${index}][producto_presentacion_id]" value="${item.producto_presentacion_id}">

            <input type="hidden" name="insumos[${index}][nombre_mostrado]" value="${item.nombre_mostrado || ''}">
            <input type="hidden" name="insumos[${index}][laboratorio]" value="${item.laboratorio || ''}">
            <input type="hidden" name="insumos[${index}][concentracion]" value="${item.concentracion || ''}">
            <input type="hidden" name="insumos[${index}][presentacion]" value="${item.presentacion || ''}">
            <input type="hidden" name="insumos[${index}][unidades_equivalentes]" value="${item.unidades_equivalentes || 1}">

            <input type="hidden" name="insumos[${index}][cantidad]" value="${item.cantidad}">
            <input type="hidden" name="insumos[${index}][unidades_necesarias]" value="${unidadesNecesarias}">
        `;
    });
}

renderInsumosServicio();
</script>

@endsection