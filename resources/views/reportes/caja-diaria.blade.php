@extends('layouts.app')

@section('title', 'Reporte de caja diaria | Santo Remedio')
@section('page-title', 'Reporte de caja diaria')
@section('page-subtitle', 'Resumen de ventas, servicios, egresos y anulaciones por caja')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Reporte de caja diaria</h2>
            <p style="margin:6px 0 0; color:#6B7280;">
                Control de ingresos por ventas y servicios en una fecha específica.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.caja-diaria.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.caja-diaria') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Fecha</label>
                <input type="date" name="fecha" value="{{ $fecha }}">
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:14px;">
            <button type="submit" class="btn-primary">
                Ver reporte
            </button>

            <a href="{{ route('reportes.caja-diaria') }}" class="btn-secondary">
                Hoy
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Ventas completadas</span>
        <h3>{{ number_format($totales['ventas_completadas'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Servicios completados</span>
        <h3>{{ number_format($totales['servicios_completados'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Ingresos válidos</span>
        <h3>{{ number_format($totales['ingresos_validos'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Egresos</span>
        <h3>{{ number_format($totales['egresos'], 2) }} Bs</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Ventas anuladas</span>
        <h3>{{ number_format($totales['ventas_anuladas'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Servicios anulados</span>
        <h3>{{ number_format($totales['servicios_anulados'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Reembolsos</span>
        <h3>{{ number_format($totales['reembolsos'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Mov. anulación</span>
        <h3>{{ number_format($totales['anulaciones'], 2) }} Bs</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Detalle por caja</h3>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Caja</th>
                    <th>Sucursal</th>
                    <th>Turno</th>
                    <th>Estado</th>
                    <th>Monto inicial</th>
                    <th>Ventas</th>
                    <th>Servicios</th>
                    <th>Egresos</th>
                    <th>Reembolsos</th>
                    <th>Efectivo caja</th>
                    <th>QR caja</th>
                    <th>Total final</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($resumenCajas as $item)
                    @php
                        $caja = $item['caja'];
                    @endphp

                    <tr>
                        <td>#{{ $caja->id }}</td>
                        <td>{{ $caja->sucursal->nombre ?? '-' }}</td>
                        <td>{{ $caja->turno->nombre ?? '-' }}</td>

                        <td>
                            @if ($caja->estado === 'abierta')
                                <span class="badge badge-success">Abierta</span>
                            @else
                                <span class="badge badge-danger">Cerrada</span>
                            @endif
                        </td>

                        <td>{{ number_format($caja->monto_inicial, 2) }} Bs</td>
                        <td>{{ number_format($item['ventas_completadas'], 2) }} Bs</td>
                        <td>{{ number_format($item['servicios_completados'], 2) }} Bs</td>
                        <td>{{ number_format($item['egresos'], 2) }} Bs</td>
                        <td>{{ number_format($item['reembolsos'], 2) }} Bs</td>
                        <td>{{ number_format($caja->total_efectivo, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_qr, 2) }} Bs</td>
                        <td>{{ number_format($caja->total_final, 2) }} Bs</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align:center; color:#6B7280;">
                            No hay cajas registradas en esta fecha.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
