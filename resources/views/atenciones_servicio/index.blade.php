@extends('layouts.app')

@section('title', 'Atenciones de servicio | Santo Remedio')
@section('page-title', 'Atenciones de servicio')
@section('page-subtitle', 'Servicios realizados y cobrados por la farmacia')

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
                <i class="bi bi-heart-pulse"></i>
                Atenciones de servicio
            </h2>

            <p>
                Historial de servicios realizados, ingresos y anulaciones.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('registrar_atencion_servicio'))
            <a href="{{ route('atenciones-servicio.create') }}" class="btn-primary">
                <i class="bi bi-plus-circle"></i>
                Registrar atención
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('atenciones-servicio.index') }}" class="filter-bar">
        <div class="filter-search">
            <label>Buscar</label>
            <input
                type="text"
                name="buscar"
                value="{{ $buscar ?? '' }}"
                placeholder="N° atención, servicio, cliente o CI/NIT"
            >
        </div>

        <div class="form-group">
            <label>Inicio</label>
            <input type="date" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}">
        </div>

        <div class="form-group">
            <label>Fin</label>
            <input type="date" name="fecha_fin" value="{{ $fechaFin ?? '' }}">
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="completada" @selected(($estado ?? '') === 'completada')>
                    Completada
                </option>
                <option value="anulada" @selected(($estado ?? '') === 'anulada')>
                    Anulada
                </option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                <i class="bi bi-x-circle"></i>
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container compact-table-container">
        <table class="table compact-table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th>Cant.</th>
                    <th>Total</th>
                    <th>Método</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th class="table-actions-cell">Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($atenciones as $atencion)
                    <tr>
                        <td>
                            <strong>
                                {{ $atencion->numero_atencion ?? 'SER-' . str_pad($atencion->id, 6, '0', STR_PAD_LEFT) }}
                            </strong>
                        </td>

                        <td>
                            <strong>{{ $atencion->servicio->nombre ?? '-' }}</strong>
                        </td>

                        <td>
                            {{ $atencion->cliente->nombre ?? 'Consumidor final' }}
                        </td>

                        <td>{{ $atencion->cantidad }}</td>

                        <td>
                            <strong>{{ number_format($atencion->total, 2) }} Bs</strong>
                        </td>

                        <td>{{ $atencion->metodoPago->nombre ?? '-' }}</td>

                        <td>{{ $atencion->usuario->nombre ?? '-' }}</td>

                        <td>
                            @if ($atencion->estado === 'completada')
                                <span class="badge badge-success">Completada</span>
                            @else
                                <span class="badge badge-danger">Anulada</span>
                            @endif
                        </td>

                        <td>{{ $atencion->fecha_hora->format('d/m/Y H:i') }}</td>

                        <td>
                            <div class="action-group">
                                <a
                                    href="{{ route('atenciones-servicio.show', $atencion) }}"
                                    class="icon-action icon-action-primary"
                                    title="Ver detalle"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a
                                    href="{{ route('atenciones-servicio.recibo', $atencion) }}"
                                    class="icon-action icon-action-print"
                                    title="Imprimir recibo"
                                    target="_blank"
                                >
                                    <i class="bi bi-printer"></i>
                                </a>

                                @if (
                                    $atencion->estado === 'completada'
                                    && auth()->user()->tienePermiso('anular_atencion_servicio')
                                )
                                    <a
                                        href="{{ route('atenciones-servicio.anular.create', $atencion) }}"
                                        class="icon-action icon-action-danger"
                                        title="Anular atención"
                                    >
                                        <i class="bi bi-x-octagon"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="empty-table-message">
                            No hay atenciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $atenciones->links() }}
    </div>
</div>

@endsection