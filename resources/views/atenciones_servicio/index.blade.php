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

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Atenciones de servicio</h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Historial de servicios realizados, ingresos y anulaciones.
            </p>
        </div>

        @if (auth()->user()->tienePermiso('registrar_atencion_servicio'))
            <a href="{{ route('atenciones-servicio.create') }}" class="btn-primary">
                + Registrar atención
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('atenciones-servicio.index') }}" style="margin-bottom:18px;">
        <div class="form-grid">
            <div class="form-group">
                <label>Buscar</label>
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar ?? '' }}"
                    placeholder="Servicio, cliente o CI/NIT"
                >
            </div>

            <div class="form-group">
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}">
            </div>

            <div class="form-group">
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin ?? '' }}">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="completada" @selected(($estado ?? '') === 'completada')>Completada</option>
                    <option value="anulada" @selected(($estado ?? '') === 'anulada')>Anulada</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px;">
            <button type="submit" class="btn-primary">
                Buscar
            </button>

            <a href="{{ route('atenciones-servicio.index') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Método</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($atenciones as $atencion)
                    <tr>
                        <td>
                            <strong>{{ $atencion->servicio->nombre ?? '-' }}</strong>
                        </td>

                        <td>
                            {{ $atencion->cliente->nombre ?? 'Consumidor final' }}
                        </td>

                        <td>{{ $atencion->cantidad }}</td>

                        <td>{{ number_format($atencion->total, 2) }} Bs</td>

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
                            <a href="{{ route('atenciones-servicio.show', $atencion) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6B7280;">
                            No hay atenciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $atenciones->links() }}
    </div>
</div>

@endsection