@extends('layouts.app')

@section('title', 'Proveedores | Santo Remedio')
@section('page-title', 'Proveedores')
@section('page-subtitle', 'Administración de laboratorios, distribuidoras y contactos de compra')

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
                <i class="bi bi-truck"></i>
                Proveedores registrados
            </h2>

            <p>
                Administre proveedores para compras, productos y registros de deuda.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('crear_proveedor'))
            <a href="{{ route('proveedores.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Nuevo proveedor
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('proveedores.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar proveedor</label>
            <input
                type="text"
                name="busqueda"
                value="{{ $busqueda }}"
                placeholder="Nombre, teléfono, contacto o dirección"
            >
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado">
                <option value="activo" @selected($estado === 'activo')>Activos</option>
                <option value="inactivo" @selected($estado === 'inactivo')>Inactivos</option>
                <option value="" @selected($estado === '')>Todos</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('proveedores.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table providers-table">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th class="table-actions-cell">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($proveedores as $proveedor)
                    <tr>
                        <td>
                            <strong>{{ $proveedor->nombre }}</strong>
                        </td>

                        <td>
                            {{ $proveedor->contacto ?? '-' }}
                        </td>

                        <td>
                            @if ($proveedor->telefono)
                                <span class="provider-phone">
                                    <i class="bi bi-telephone"></i>
                                    {{ $proveedor->telefono }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <span class="provider-address">
                                {{ $proveedor->direccion ?? '-' }}
                            </span>
                        </td>

                        <td>
                            @if ($proveedor->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-group">
                                @if (auth()->user()->tienePermiso('ver_proveedores'))
                                    <a
                                        href="{{ route('proveedores.show', $proveedor) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver proveedor"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif

                                @if (auth()->user()->tienePermiso('editar_proveedor'))
                                    <a
                                        href="{{ route('proveedores.edit', $proveedor) }}"
                                        class="icon-action icon-action-edit"
                                        title="Editar proveedor"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                @if ($proveedor->estado === 'activo' && auth()->user()->tienePermiso('eliminar_proveedor'))
                                    <form
                                        method="POST"
                                        action="{{ route('proveedores.destroy', $proveedor) }}"
                                        class="form-desactivar-proveedor"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="icon-action icon-action-danger"
                                            title="Desactivar proveedor"
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
                        <td colspan="6" class="empty-table-message">
                            No hay proveedores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $proveedores->links() }}
    </div>

</div>

<script>
document.querySelectorAll('.form-desactivar-proveedor').forEach(form => {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: '¿Desactivar proveedor?',
            text: 'El proveedor quedará inactivo y no debería usarse para nuevas compras.',
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