@extends('layouts.app')

@section('title', 'Ingresos diarios | Santo Remedio')
@section('page-title', 'Ingresos diarios')
@section('page-subtitle', 'Resumen general de ventas y servicios de farmacia')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Ingresos diarios</h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Resumen general de ingresos por ventas y servicios.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.ingresos-diarios.exportar-csv', request()->query()) }}"
                class="btn-primary"
            >
                Exportar Excel
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                Volver a reportes
            </a>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 22px;">
    <h3 style="margin-top:0; color:#4C1D95;">Filtros</h3>

    <form method="GET" action="{{ route('reportes.ingresos-diarios') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.ingresos-diarios') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Ventas completadas</span>
        <h3>{{ number_format($resumen['total_ventas'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Servicios completados</span>
        <h3>{{ number_format($resumen['total_servicios'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Ingresos totales</span>
        <h3>{{ number_format($resumen['ingresos_totales'], 2) }} Bs</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Cantidad de ventas</span>
        <h3>{{ $resumen['cantidad_ventas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Atenciones de servicio</span>
        <h3>{{ $resumen['cantidad_servicios'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Ventas anuladas</span>
        <h3>{{ $resumen['ventas_anuladas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Servicios anulados</span>
        <h3>{{ $resumen['servicios_anulados'] }}</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Monto ventas anuladas</span>
        <h3>{{ number_format($resumen['monto_ventas_anuladas'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Monto servicios anulados</span>
        <h3>{{ number_format($resumen['monto_servicios_anulados'], 2) }} Bs</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle de servicios registrados</h3>

    <p style="color:#6B7280;">
        Este detalle muestra las atenciones de servicio dentro del rango seleccionado. Las ventas completas se revisan desde el reporte de ventas.
    </p>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th>Método</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Ver</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($detalleServicios as $atencion)
                    <tr>
                        <td>{{ $atencion->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td>{{ $atencion->servicio->nombre ?? '-' }}</td>
                        <td>{{ $atencion->cliente->nombre ?? 'Consumidor final' }}</td>
                        <td>{{ $atencion->metodoPago->nombre ?? '-' }}</td>
                        <td>{{ number_format($atencion->total, 2) }} Bs</td>

                        <td>
                            @if ($atencion->estado === 'completada')
                                <span class="badge badge-success">Completada</span>
                            @else
                                <span class="badge badge-danger">Anulada</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('atenciones-servicio.show', $atencion) }}" class="btn-secondary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; color:#6B7280;">
                            No hay servicios registrados en este rango.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:18px;">
        {{ $detalleServicios->links() }}
    </div>
</div>

@endsection