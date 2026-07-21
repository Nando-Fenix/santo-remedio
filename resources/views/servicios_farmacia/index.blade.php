@extends('layouts.app')

@section('title', 'Servicios de farmacia | Santo Remedio')
@section('page-title', 'Servicios de farmacia')
@section('page-subtitle', 'Administración de servicios cobrados por la farmacia')

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

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0; color: #4C1D95;">Servicios de farmacia</h2>
            <p style="margin: 6px 0 0; color: #6B7280;">
                Configure servicios como inyectables, controles, curaciones y otros.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('crear_servicio_farmacia'))
            <a href="{{ route('servicios-farmacia.create') }}" class="btn-primary">
                + Nuevo servicio
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('servicios-farmacia.index') }}" style="margin-bottom: 18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar servicio</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Nombre o descripción"
                >
            </div>

            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="inyectable" @selected(($tipo ?? '') === 'inyectable')>Inyectable</option>
                    <option value="control" @selected(($tipo ?? '') === 'control')>Control</option>
                    <option value="curacion" @selected(($tipo ?? '') === 'curacion')>Curación</option>
                    <option value="nebulizacion" @selected(($tipo ?? '') === 'nebulizacion')>Nebulización</option>
                    <option value="orientacion" @selected(($tipo ?? '') === 'orientacion')>Orientación</option>
                    <option value="otro" @selected(($tipo ?? '') === 'otro')>Otro</option>
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
        </div>

        <div style="display: flex; gap: 10px; margin-top: 14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('servicios-farmacia.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Tipo</th>
                    <th>Precio</th>
                    <th>Insumos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($servicios as $servicio)
                    <tr>
                        <td>
                            <strong>{{ $servicio->nombre }}</strong>
                            @if ($servicio->descripcion)
                                <br>
                                <small style="color: #6B7280;">
                                    {{ Str::limit($servicio->descripcion, 80) }}
                                </small>
                            @endif
                        </td>

                        <td>
                            @if ($servicio->tipo === 'inyectable')
                                Inyectable
                            @elseif ($servicio->tipo === 'control')
                                Control
                            @elseif ($servicio->tipo === 'curacion')
                                Curación
                            @elseif ($servicio->tipo === 'nebulizacion')
                                Nebulización
                            @elseif ($servicio->tipo === 'orientacion')
                                Orientación
                            @else
                                Otro
                            @endif
                        </td>

                        <td>{{ number_format($servicio->precio, 2) }} Bs</td>

                        <td>{{ $servicio->insumos_count }}</td>

                        <td>
                            @if ($servicio->estado === 'activo')
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('servicios-farmacia.show', $servicio) }}" class="btn-secondary">
                                    Ver
                                </a>

                                @if (auth()->user()->tienePermiso('editar_servicio_farmacia'))
                                    <a href="{{ route('servicios-farmacia.edit', $servicio) }}" class="btn-primary">
                                        Editar
                                    </a>
                                @endif

                                @if ($servicio->estado === 'activo' && auth()->user()->tienePermiso('desactivar_servicio_farmacia'))
                                    <form method="POST" action="{{ route('servicios-farmacia.destroy', $servicio) }}" class="form-desactivar-servicio">
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
                            No hay servicios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 18px;">
        {{ $servicios->links() }}
    </div>
</div>

<script>
document.querySelectorAll('.form-desactivar-servicio').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: '¿Desactivar servicio?',
            text: 'El servicio ya no estará disponible para nuevas atenciones.',
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
