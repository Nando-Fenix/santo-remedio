@extends('layouts.app')

@section('title', 'Promociones | Santo Remedio')
@section('page-title', 'Promociones')
@section('page-subtitle', 'Ofertas, combos y promociones por vencimiento')

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
                <i class="bi bi-tags"></i>
                Promociones
            </h2>

            <p>
                Administre promociones individuales, combos y productos próximos a vencer.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('crear_promocion'))
            <a href="{{ route('promociones.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Nueva promoción
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('promociones.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar promoción</label>
            <input
                type="text"
                name="buscar"
                value="{{ $buscar ?? '' }}"
                placeholder="Nombre, descripción o motivo"
            >
        </div>

        <div class="form-group">
            <label>Tipo</label>
            <select name="tipo">
                <option value="">Todos</option>
                <option value="producto_individual" @selected(($tipo ?? '') === 'producto_individual')>
                    Producto individual
                </option>
                <option value="combo" @selected(($tipo ?? '') === 'combo')>
                    Combo
                </option>
                <option value="por_vencimiento" @selected(($tipo ?? '') === 'por_vencimiento')>
                    Por vencimiento
                </option>
            </select>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="activo" @selected(($estado ?? '') === 'activo')>Activo</option>
                <option value="inactivo" @selected(($estado ?? '') === 'inactivo')>Inactivo</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('promociones.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table">
            <thead>
                <tr>
                    <th>Promoción</th>
                    <th>Tipo</th>
                    <th>Precio</th>
                    <th>Vigencia</th>
                    <th>Productos</th>
                    <th>Sucursal</th>
                    <th>Estado</th>
                    <th class="table-actions-cell">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($promociones as $promocion)
                    <tr>
                        <td>
                            <strong>{{ $promocion->nombre }}</strong>

                            @if ($promocion->descripcion)
                                <br>
                                <small style="color:#6B7280;">
                                    {{ Str::limit($promocion->descripcion, 90) }}
                                </small>
                            @endif
                        </td>

                        <td>
                            @if ($promocion->tipo === 'producto_individual')
                                Producto individual
                            @elseif ($promocion->tipo === 'combo')
                                Combo
                            @elseif ($promocion->tipo === 'por_vencimiento')
                                Por vencimiento
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <strong>{{ number_format($promocion->precio_promocional, 2) }} Bs</strong>
                        </td>

                        <td>
                            @if ($promocion->fecha_inicio || $promocion->fecha_fin)
                                <small>
                                    {{ $promocion->fecha_inicio ? $promocion->fecha_inicio->format('d/m/Y') : 'Sin inicio' }}
                                    <br>
                                    {{ $promocion->fecha_fin ? $promocion->fecha_fin->format('d/m/Y') : 'Sin fin' }}
                                </small>
                            @else
                                <small style="color:#6B7280;">Sin vigencia</small>
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-info">
                                {{ $promocion->items_count }} producto(s)
                            </span>
                        </td>

                        <td>
                            {{ $promocion->sucursal->nombre ?? 'Todas' }}
                        </td>

                        <td>
                            <span class="badge {{ $promocion->estado === 'activo' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($promocion->estado) }}
                            </span>
                        </td>

                        <td>
                            <div class="action-group">
                                @if (auth()->user()->tienePermiso('ver_promociones'))
                                    <a
                                        href="{{ route('promociones.show', $promocion) }}"
                                        class="icon-action icon-action-primary"
                                        title="Ver detalle"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif

                                @if ($promocion->estado === 'activo' && auth()->user()->tienePermiso('editar_promocion'))
                                    <a
                                        href="{{ route('promociones.edit', $promocion) }}"
                                        class="icon-action icon-action-edit"
                                        title="Editar promoción"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                @if ($promocion->estado === 'activo' && auth()->user()->tienePermiso('desactivar_promocion'))
                                    <form
                                        method="POST"
                                        action="{{ route('promociones.destroy', $promocion) }}"
                                        class="form-desactivar-promocion"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="icon-action icon-action-danger"
                                            title="Desactivar promoción"
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
                            Todavía no hay promociones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $promociones->links() }}
    </div>

</div>

<script>
document.querySelectorAll('.form-desactivar-promocion').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: '¿Desactivar promoción?',
            text: 'La promoción ya no estará disponible para nuevas ventas.',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

@endsection