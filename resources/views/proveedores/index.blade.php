@extends('layouts.app')

@section('title', 'Proveedores | Santo Remedio')
@section('page-title', 'Proveedores')
@section('page-subtitle', 'Administración de laboratorios, distribuidoras y contactos de compra')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Proveedores registrados</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Administre proveedores para compras, productos y futuros registros de deuda.
            </p>
        </div>

        <a href="{{ route('proveedores.create') }}" class="btn-primary">
            Nuevo proveedor
        </a>
    </div>

    <form method="GET" action="{{ route('proveedores.index') }}" style="margin-top: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar proveedor</label>
                <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Nombre, teléfono, contacto o dirección">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="activo" {{ $estado === 'activo' ? 'selected' : '' }}>Activos</option>
                    <option value="inactivo" {{ $estado === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
                    <option value="" {{ $estado === '' ? 'selected' : '' }}>Todos</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('proveedores.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th style="width: 220px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proveedores as $proveedor)
                    <tr>
                        <td><strong>{{ $proveedor->nombre }}</strong></td>
                        <td>{{ $proveedor->contacto ?? '-' }}</td>
                        <td>{{ $proveedor->telefono ?? '-' }}</td>
                        <td>{{ $proveedor->direccion ?? '-' }}</td>
                        <td>
                            @if ($proveedor->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('proveedores.show', $proveedor) }}" class="btn-secondary">
                                    Ver
                                </a>

                                <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn-secondary">
                                    Editar
                                </a>

                                @if ($proveedor->estado === 'activo')
                                    <form method="POST" action="{{ route('proveedores.destroy', $proveedor) }}" onsubmit="return confirmarFormulario(event, '¿Desea desactivar este proveedor?')">
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
                        <td colspan="6" style="text-align: center; color: #6B7280;">
                            No hay proveedores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $proveedores->links() }}
    </div>
</div>

@endsection