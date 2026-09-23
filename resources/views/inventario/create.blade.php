@extends('layouts.app')

@section('title', 'Entrada de inventario | Santo Remedio')
@section('page-title', 'Entrada de inventario')
@section('page-subtitle', 'Registro inicial o ingreso manual de stock')

@section('content')

@if (!auth()->user()->tienePermiso('ajustar_inventario'))
    <div class="alert-danger">
        No tiene permiso para registrar entradas de inventario.
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

<form method="POST" action="{{ route('inventario.store') }}" class="inventory-form" id="form_entrada_inventario">
    @csrf

    <div class="inventory-layout">

        <section class="inventory-main">

            <div class="inventory-header-card">
                <div>
                    <h2>
                        <i class="bi bi-box-arrow-in-down"></i>
                        Registrar entrada de inventario
                    </h2>

                    <p>
                        Use esta pantalla para cargar stock inicial o registrar ingresos manuales de productos.
                    </p>
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-capsule"></i>
                            Producto y sucursal
                        </h3>
                        <small>Seleccione el producto que ingresará al inventario</small>
                    </div>
                </div>

                <div class="inventory-product-branch-grid">
                    <div class="form-group">
                        <div class="inventory-label-action">
                            <label>Producto *</label>

                            @if (auth()->user()->tienePermiso('crear_producto'))
                                <button type="button" class="inventory-link-button" onclick="abrirModalProductoRapido()">
                                    <i class="bi bi-plus-circle"></i>
                                    Nuevo producto
                                </button>
                            @endif
                        </div>

                        <select name="producto_id" id="producto_id" required>
                            <option value="">Seleccione un producto</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}" @selected(old('producto_id') == $producto->id)>
                                    {{ $producto->nombre_comercial }}
                                    @if($producto->concentracion)
                                        - {{ $producto->concentracion }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Sucursal *</label>
                        <select name="sucursal_id" required>
                            <option value="">Seleccione una sucursal</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" @selected(old('sucursal_id') == $sucursal->id)>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="inventory-card">
                <div class="inventory-section-head">
                    <div>
                        <h3>
                            <i class="bi bi-upc-scan"></i>
                            Lote y vencimiento
                        </h3>
                        <small>Datos útiles para control FEFO y alertas de vencimiento</small>
                    </div>
                </div>

                <div class="inventory-grid">
                    <div class="form-group">
                        <label>Número de lote</label>
                        <input
                            type="text"
                            name="numero_lote"
                            value="{{ old('numero_lote') }}"
                            placeholder="Ej: A001"
                        >
                    </div>

                    <div class="form-group">
                        <label>Fecha de vencimiento</label>
                        <input
                            type="date"
                            name="fecha_vencimiento"
                            value="{{ old('fecha_vencimiento') }}"
                        >
                    </div>
                </div>
            </div>

        </section>

        <aside class="inventory-summary">

            <div class="inventory-summary-card">
                <h3>
                    <i class="bi bi-boxes"></i>
                    Cantidades
                </h3>

                <div class="inventory-side-grid">
                    <div class="form-group">
                        <label>Cantidad a ingresar *</label>
                        <input
                            type="number"
                            min="1"
                            name="cantidad"
                            value="{{ old('cantidad', 1) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Stock mínimo *</label>
                        <input
                            type="number"
                            min="0"
                            name="stock_minimo"
                            value="{{ old('stock_minimo', 0) }}"
                            required
                        >
                    </div>
                </div>

                <div class="inventory-help-card">
                    <i class="bi bi-lightbulb"></i>
                    <span>
                        El stock mínimo ayuda a detectar productos que necesitan reposición.
                    </span>
                </div>
            </div>

            <div class="inventory-summary-card">
                <h3>
                    <i class="bi bi-card-text"></i>
                    Motivo
                </h3>

                <div class="form-group">
                    <label>Motivo / observación</label>
                    <textarea
                        name="motivo"
                        rows="5"
                        placeholder="Ej: Entrada inicial de inventario"
                    >{{ old('motivo') }}</textarea>
                </div>
            </div>

            <div class="inventory-actions">
                @if (auth()->user()->tienePermiso('ver_inventario'))
                    <a href="{{ route('inventario.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>
                @endif

                @if (auth()->user()->tienePermiso('ajustar_inventario'))
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check2-circle"></i>
                        Guardar entrada
                    </button>
                @endif
            </div>

        </aside>

    </div>
</form>

@if (auth()->user()->tienePermiso('crear_producto'))
    <div class="inventory-product-modal" id="modal_producto_rapido">
        <div class="inventory-product-modal-card">
            <div class="inventory-product-modal-head">
                <div>
                    <h3>
                        <i class="bi bi-plus-circle"></i>
                        Nuevo producto rápido
                    </h3>
                    <p>Registre un producto básico para usarlo en esta entrada de inventario.</p>
                </div>

                <button type="button" onclick="cerrarModalProductoRapido()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="inventory-product-modal-body">
                <div class="form-group">
                    <label>Nombre comercial *</label>
                    <input type="text" id="producto_rapido_nombre" placeholder="Ej: Paracetamol">
                </div>

                <div class="form-group">
                    <label>Nombre genérico</label>
                    <input type="text" id="producto_rapido_generico" placeholder="Ej: Paracetamol">
                </div>

                <div class="form-group">
                    <label>Concentración</label>
                    <input type="text" id="producto_rapido_concentracion" placeholder="Ej: 500mg">
                </div>

                <div class="form-group">
                    <label>Tipo de producto *</label>
                    <select id="producto_rapido_tipo">
                        <option value="medicamento">Medicamento</option>
                        <option value="insumo_medico">Insumo médico</option>
                        <option value="producto_general">Producto general</option>
                        <option value="higiene">Higiene</option>
                        <option value="bebe">Bebé</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <div class="inventory-label-action">
                        <label>Categoría *</label>

                        <button type="button" class="inventory-link-button" onclick="mostrarNuevaCategoriaRapida()">
                            <i class="bi bi-plus-circle"></i>
                            Nueva categoría
                        </button>
                    </div>

                    <select id="producto_rapido_categoria">
                        <option value="">Seleccione una categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="inventory-new-category-box" id="nueva_categoria_box">
                    <div class="form-group">
                        <label>Nombre de nueva categoría</label>
                        <input
                            type="text"
                            id="nueva_categoria_nombre"
                            placeholder="Ej: Antibióticos"
                        >
                    </div>

                    <button type="button" class="btn-secondary" onclick="guardarCategoriaRapida()">
                        <i class="bi bi-check2-circle"></i>
                        Crear categoría
                    </button>
                </div>
            </div>

            <div class="inventory-product-modal-actions">
                <button type="button" class="btn-secondary" onclick="cerrarModalProductoRapido()">
                    Cancelar
                </button>

                <button type="button" class="btn-primary" onclick="guardarProductoRapido()">
                    <i class="bi bi-check2-circle"></i>
                    Guardar producto
                </button>
            </div>
        </div>
    </div>
@endif
@endif
<script>
const formEntradaInventario = document.getElementById('form_entrada_inventario');

if (formEntradaInventario) {
    formEntradaInventario.addEventListener('submit', function (event) {
        event.preventDefault();

        const producto = document.querySelector('select[name="producto_id"]').value;
        const sucursal = document.querySelector('select[name="sucursal_id"]').value;
        const cantidad = Number(document.querySelector('input[name="cantidad"]').value || 0);
        const stockMinimo = Number(document.querySelector('input[name="stock_minimo"]').value || 0);

        if (!producto) {
            Swal.fire({
                icon: 'warning',
                title: 'Producto requerido',
                text: 'Debe seleccionar un producto.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        if (!sucursal) {
            Swal.fire({
                icon: 'warning',
                title: 'Sucursal requerida',
                text: 'Debe seleccionar una sucursal.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        if (cantidad <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cantidad inválida',
                text: 'La cantidad a ingresar debe ser mayor a 0.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        if (stockMinimo < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Stock mínimo inválido',
                text: 'El stock mínimo no puede ser negativo.',
                confirmButtonColor: '#6D28D9'
            });

            return;
        }

        Swal.fire({
            icon: 'question',
            title: '¿Guardar entrada de inventario?',
            text: 'Se registrará el ingreso de stock para el producto seleccionado.',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#6D28D9',
            cancelButtonColor: '#6B7280'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit();
            }
        });
    });
}

function abrirModalProductoRapido() {
    const modal = document.getElementById('modal_producto_rapido');

    if (!modal) {
        Swal.fire({
            icon: 'error',
            title: 'Modal no encontrado',
            text: 'No se encontró el modal de producto rápido.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    modal.classList.add('active');

    setTimeout(() => {
        const inputNombre = document.getElementById('producto_rapido_nombre');

        if (inputNombre) {
            inputNombre.focus();
        }
    }, 100);
}

function cerrarModalProductoRapido() {
    const modal = document.getElementById('modal_producto_rapido');

    if (modal) {
        modal.classList.remove('active');
    }
}

function limpiarProductoRapido() {
    const nombre = document.getElementById('producto_rapido_nombre');
    const generico = document.getElementById('producto_rapido_generico');
    const concentracion = document.getElementById('producto_rapido_concentracion');
    const tipo = document.getElementById('producto_rapido_tipo');
    const categoria = document.getElementById('producto_rapido_categoria');

    if (nombre) nombre.value = '';
    if (generico) generico.value = '';
    if (concentracion) concentracion.value = '';
    if (tipo) tipo.value = 'medicamento';
    if (categoria) categoria.value = '';
}

function mostrarNuevaCategoriaRapida() {
    const box = document.getElementById('nueva_categoria_box');

    if (!box) {
        return;
    }

    box.classList.toggle('active');

    if (box.classList.contains('active')) {
        const inputCategoria = document.getElementById('nueva_categoria_nombre');

        if (inputCategoria) {
            inputCategoria.focus();
        }
    }
}

async function guardarCategoriaRapida() {
    const inputCategoria = document.getElementById('nueva_categoria_nombre');

    if (!inputCategoria) {
        return;
    }

    const nombreCategoria = inputCategoria.value.trim();

    if (nombreCategoria.length < 3) {
        Swal.fire({
            icon: 'warning',
            title: 'Nombre requerido',
            text: 'La categoría debe tener al menos 3 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    try {
        const response = await fetch("{{ route('inventario.categoria-rapida') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nombre: nombreCategoria
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'No se pudo crear la categoría.');
        }

        const selectCategoria = document.getElementById('producto_rapido_categoria');

        if (selectCategoria) {
            const option = new Option(data.categoria.nombre, data.categoria.id, true, true);
            selectCategoria.add(option);
            selectCategoria.value = data.categoria.id;
        }

        inputCategoria.value = '';

        const box = document.getElementById('nueva_categoria_box');

        if (box) {
            box.classList.remove('active');
        }

        Swal.fire({
            icon: 'success',
            title: 'Categoría creada',
            text: 'La categoría fue agregada y seleccionada correctamente.',
            confirmButtonColor: '#6D28D9'
        });

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            confirmButtonColor: '#6D28D9'
        });
    }
}

async function guardarProductoRapido() {
    const inputNombre = document.getElementById('producto_rapido_nombre');
    const inputGenerico = document.getElementById('producto_rapido_generico');
    const inputConcentracion = document.getElementById('producto_rapido_concentracion');
    const inputTipo = document.getElementById('producto_rapido_tipo');
    const inputCategoria = document.getElementById('producto_rapido_categoria');

    if (!inputNombre || !inputTipo || !inputCategoria) {
        Swal.fire({
            icon: 'error',
            title: 'Formulario incompleto',
            text: 'Faltan campos necesarios en el modal.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    const nombre = inputNombre.value.trim();
    const generico = inputGenerico ? inputGenerico.value.trim() : '';
    const concentracion = inputConcentracion ? inputConcentracion.value.trim() : '';
    const tipo = inputTipo.value;
    const categoriaId = inputCategoria.value;

    if (nombre.length < 3) {
        Swal.fire({
            icon: 'warning',
            title: 'Nombre requerido',
            text: 'El nombre comercial debe tener al menos 3 caracteres.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    if (!categoriaId) {
        Swal.fire({
            icon: 'warning',
            title: 'Categoría requerida',
            text: 'Debe seleccionar o crear una categoría para el producto.',
            confirmButtonColor: '#6D28D9'
        });

        return;
    }

    try {
        const response = await fetch("{{ route('inventario.producto-rapido') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nombre_comercial: nombre,
                nombre_generico: generico,
                concentracion: concentracion,
                tipo_producto: tipo,
                categoria_id: categoriaId
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'No se pudo crear el producto.');
        }

        const selectProducto = document.getElementById('producto_id');

        if (selectProducto) {
            const option = new Option(data.producto.nombre, data.producto.id, true, true);
            selectProducto.add(option);
            selectProducto.value = data.producto.id;
        }

        limpiarProductoRapido();
        cerrarModalProductoRapido();

        Swal.fire({
            icon: 'success',
            title: 'Producto creado',
            text: 'El producto fue agregado y seleccionado correctamente.',
            confirmButtonColor: '#6D28D9'
        });

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            confirmButtonColor: '#6D28D9'
        });
    }
}
</script>

@endsection