@extends('layouts.app')

@section('title', 'Productos | Santo Remedio')
@section('page-title', 'Productos')
@section('page-subtitle', 'Registro y consulta de productos listos para venta')

@section('content')

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="compact-card">

    <div class="compact-header">
        <div>
            <h2>
                <i class="bi bi-capsule"></i>
                Listado de productos
            </h2>

            <p>
                Medicamentos y productos registrados en el sistema.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('crear_producto'))
            <a href="{{ route('productos.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Nuevo producto
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('productos.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar producto</label>
            <input
                type="text"
                name="buscar"
                value="{{ $buscar ?? '' }}"
                placeholder="Nombre, genérico, concentración, categoría, laboratorio..."
            >
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="activo" @selected(($estado ?? '') === 'activo')>Activo</option>
                <option value="inactivo" @selected(($estado ?? '') === 'inactivo')>Inactivo</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tipo</label>
            <select name="tipo_producto">
                <option value="">Todos</option>
                <option value="medicamento" @selected(($tipoProducto ?? '') === 'medicamento')>Medicamento</option>
                <option value="insumo_medico" @selected(($tipoProducto ?? '') === 'insumo_medico')>Insumo médico</option>
                <option value="producto_general" @selected(($tipoProducto ?? '') === 'producto_general')>Producto general</option>
                <option value="higiene" @selected(($tipoProducto ?? '') === 'higiene')>Higiene</option>
                <option value="bebe" @selected(($tipoProducto ?? '') === 'bebe')>Bebé</option>
                <option value="otro" @selected(($tipoProducto ?? '') === 'otro')>Otro</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('productos.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table products-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Categoría</th>
                    <th>Lab / Marca</th>
                    <th>Presentación</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Pres.</th>
                    <th>Estado</th>
                    <th class="table-actions-cell">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($productos as $producto)
                    @php
                        $presentacionPrincipal = $producto->presentacionPrincipal;
                        $stockTotal = $producto->inventarios->sum('stock_actual');

                        $tiposProducto = [
                            'medicamento' => 'Medicamento',
                            'insumo_medico' => 'Insumo médico',
                            'producto_general' => 'Producto general',
                            'higiene' => 'Higiene',
                            'bebe' => 'Bebé',
                            'otro' => 'Otro',
                        ];
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $producto->nombre_comercial }}</strong>

                            @if ($producto->concentracion)
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $producto->concentracion }}
                                </small>
                            @endif

                            @if ($producto->nombre_generico)
                                <br>
                                <small style="color:#6B7280;">
                                    Genérico: {{ $producto->nombre_generico }}
                                </small>
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-soft">
                                {{ $tiposProducto[$producto->tipo_producto] ?? 'Sin tipo' }}
                            </span>
                        </td>

                        <td>
                            {{ $producto->categoria->nombre ?? '-' }}
                        </td>

                        <td>
                            {{ $producto->laboratorio->nombre ?? '-' }}
                        </td>

                        <td>
                            @if ($presentacionPrincipal)
                                <strong>{{ $presentacionPrincipal->nombre_mostrado }}</strong>
                                <br>
                                <small style="color:#6B7280;">
                                    {{ $presentacionPrincipal->unidades_equivalentes }} unidad(es)
                                </small>
                            @else
                                <span class="product-no-presentation">
                                    Sin presentación
                                </span>
                            @endif
                        </td>

                        <td>
                            @if ($presentacionPrincipal)
                                <strong>{{ number_format($presentacionPrincipal->precio_venta, 2) }} Bs</strong>
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if ($stockTotal > 0)
                                <strong class="product-stock-ok">{{ $stockTotal }}</strong>
                            @else
                                <strong class="product-stock-empty">0</strong>
                            @endif
                        </td>

                        <td>
                            <strong>{{ $producto->presentaciones_count }}</strong>
                        </td>

                        <td>
                            @if ($producto->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-group">
                                @if (auth()->user()->tienePermiso('ver_productos'))
                                    <a
                                        href="{{ route('productos.presentaciones.index', $producto) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver presentaciones"
                                    >
                                        <i class="bi bi-layers"></i>
                                    </a>
                                @endif

                                @if (auth()->user()->tienePermiso('editar_producto'))
                                    <a
                                        href="{{ route('productos.edit', $producto) }}"
                                        class="icon-action icon-action-edit"
                                        title="Editar producto"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                @if ($producto->estado === 'activo' && auth()->user()->tienePermiso('desactivar_producto'))
                                    <form
                                        method="POST"
                                        action="{{ route('productos.destroy', $producto) }}"
                                        class="form-desactivar-producto"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="icon-action icon-action-danger"
                                            title="Desactivar producto"
                                        >
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="empty-table-message">
                            Todavía no hay productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $productos->links() }}
    </div>

</div>

<script>
document.querySelectorAll('.form-desactivar-producto').forEach(form => {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: '¿Desactivar producto?',
            text: 'El producto quedará inactivo y no debería usarse para nuevas ventas.',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit();
            }
        });
    });
});
</script>

@endsection