@extends('layouts.app')

@section('title', 'Clientes | Santo Remedio')
@section('page-title', 'Clientes')
@section('page-subtitle', 'Registro y administración de clientes de la farmacia')

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
                <i class="bi bi-people"></i>
                Clientes registrados
            </h2>

            <p>
                Administre los datos de clientes para historial, descuentos y fidelización.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('crear_cliente'))
            <a href="{{ route('clientes.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Nuevo cliente
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('clientes.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar cliente</label>
            <input
                type="text"
                name="busqueda"
                value="{{ $busqueda }}"
                placeholder="Nombre, CI/NIT, teléfono o dirección"
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

            <a href="{{ route('clientes.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table clients-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>CI/NIT</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Tipo</th>
                    <th>Descuento</th>
                    <th>Estado</th>
                    <th class="table-actions-cell">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($clientes as $cliente)
                    <tr>
                        <td>
                            <strong>{{ $cliente->nombre }}</strong>
                        </td>

                        <td>
                            {{ $cliente->ci_nit ?? '-' }}
                        </td>

                        <td>
                            @if ($cliente->telefono)
                                <span class="client-phone">
                                    <i class="bi bi-telephone"></i>
                                    {{ $cliente->telefono }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <span class="client-address">
                                {{ $cliente->direccion ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="badge badge-soft">
                                {{ ucfirst($cliente->tipo_cliente) }}
                            </span>
                        </td>

                        <td>
                            <strong>{{ number_format($cliente->descuento_default, 2) }}%</strong>
                        </td>

                        <td>
                            @if ($cliente->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-group">
                                @if (auth()->user()->tienePermiso('ver_clientes'))
                                    <a
                                        href="{{ route('clientes.show', $cliente) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver cliente"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif

                                @if (auth()->user()->tienePermiso('editar_cliente'))
                                    <a
                                        href="{{ route('clientes.edit', $cliente) }}"
                                        class="icon-action icon-action-edit"
                                        title="Editar cliente"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                @if ($cliente->estado === 'activo' && auth()->user()->tienePermiso('eliminar_cliente'))
                                    <form
                                        method="POST"
                                        action="{{ route('clientes.destroy', $cliente) }}"
                                        class="form-desactivar-cliente"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="icon-action icon-action-danger"
                                            title="Desactivar cliente"
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
                        <td colspan="8" class="empty-table-message">
                            No hay clientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $clientes->links() }}
    </div>

</div>

<script>
document.querySelectorAll('.form-desactivar-cliente').forEach(form => {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: '¿Desactivar cliente?',
            text: 'El cliente quedará inactivo y no debería usarse en nuevas ventas.',
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