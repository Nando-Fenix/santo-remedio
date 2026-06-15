@extends('layouts.app')

@section('title', 'Productos | Santo Remedio')
@section('page-title', 'Productos')
@section('page-subtitle', 'Registro y consulta de medicamentos')

@section('content')

<div class="card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Listado de productos</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Medicamentos y productos registrados en el sistema.
            </p>
        </div>

        <a href="{{ route('productos.create') }}" class="btn-primary">
            + Nuevo producto
        </a>
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
                    <th>Nombre comercial</th>
                    <th>Nombre genérico</th>
                    <th>Concentración</th>
                    <th>Categoría</th>
                    <th>Laboratorio</th>
                    <th>Proveedor</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre_comercial }}</td>
                        <td>{{ $producto->nombre_generico ?? '-' }}</td>
                        <td>{{ $producto->concentracion ?? '-' }}</td>
                        <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                        <td>{{ $producto->laboratorio->nombre ?? '-' }}</td>
                        <td>{{ $producto->proveedor->nombre ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $producto->estado === 'activo' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($producto->estado) }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('productos.presentaciones.index', $producto) }}" class="btn-secondary">
                                    Presentaciones
                                </a>

                                <a href="{{ route('productos.edit', $producto) }}" class="btn-secondary">
                                    Editar
                                </a>

                                @if ($producto->estado === 'activo')
                                    <form method="POST" action="{{ route('productos.destroy', $producto) }}" onsubmit="return confirm('¿Desea desactivar este producto?')">
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
                        <td colspan="8" style="text-align: center; color: #6B7280;">
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