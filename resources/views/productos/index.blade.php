@extends('layouts.app')

@section('title', 'Productos | Santo Remedio')
@section('page-title', 'Productos')
@section('page-subtitle', 'Registro y consulta de productos listos para venta')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Listado de productos</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Medicamentos y productos registrados en el sistema.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('crear_producto'))
            <a href="{{ route('productos.create') }}" class="btn-primary">
                + Nuevo producto
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('productos.index') }}" style="margin-bottom: 18px;">
            <div class="form-grid">
                <div class="form-group">
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
                    <label>Tipo de producto</label>
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
            </div>

            <div style="display: flex; gap: 10px; margin-top: 14px;">
                <button type="submit" class="btn-primary">
                    Buscar
                </button>

                <a href="{{ route('productos.index') }}" class="btn-secondary">
                    Limpiar
                </a>
            </div>
        </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Categoría</th>
                    <th>Laboratorio / Marca</th>
                    <th>Presentación principal</th>
                    <th>Precio venta</th>
                    <th>Stock</th>
                    <th>Presentaciones</th>
                    <th>Estado</th>
                    <th>Acciones</th>
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
                                <div style="font-size: 13px; color: #6B7280;">
                                    {{ $producto->concentracion }}
                                </div>
                            @endif

                            @if ($producto->nombre_generico)
                                <div style="font-size: 13px; color: #6B7280;">
                                    Genérico: {{ $producto->nombre_generico }}
                                </div>
                            @endif
                        </td>

                        <td>
                            {{ $tiposProducto[$producto->tipo_producto] ?? 'Sin tipo' }}
                        </td>

                        <td>{{ $producto->categoria->nombre ?? '-' }}</td>

                        <td>{{ $producto->laboratorio->nombre ?? '-' }}</td>

                        <td>
                            @if ($presentacionPrincipal)
                                <strong>{{ $presentacionPrincipal->nombre_mostrado }}</strong>

                                <div style="font-size: 13px; color: #6B7280;">
                                    Equivale a {{ $presentacionPrincipal->unidades_equivalentes }} unidad(es)
                                </div>
                            @else
                                <span style="color: #DC2626;">Sin presentación</span>
                            @endif
                        </td>

                        <td>
                            @if ($presentacionPrincipal)
                                {{ number_format($presentacionPrincipal->precio_venta, 2) }} Bs
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <strong>{{ $stockTotal }}</strong>
                        </td>

                        <td>
                            {{ $producto->presentaciones_count }}
                        </td>

                        <td>
                            <span class="badge {{ $producto->estado === 'activo' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($producto->estado) }}
                            </span>
                        </td>

                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @if (auth()->user()->tienePermiso('ver_productos'))
                                    <a href="{{ route('productos.presentaciones.index', $producto) }}" class="btn-secondary">
                                        Presentaciones
                                    </a>
                                @endif

                                @if (auth()->user()->tienePermiso('editar_producto'))
                                    <a href="{{ route('productos.edit', $producto) }}" class="btn-secondary">
                                        Editar
                                    </a>
                                @endif

                                @if ($producto->estado === 'activo' && auth()->user()->tienePermiso('desactivar_producto'))
                                    <form method="POST"
                                        action="{{ route('productos.destroy', $producto) }}"
                                        onsubmit="return confirmarFormulario(event, '¿Desea desactivar este producto?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-danger">
                                            Desactivar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: #6B7280;">
                            Todavía no hay productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $productos->links() }}
    </div>

</div>

@endsection