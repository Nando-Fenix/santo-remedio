@extends('layouts.app')

@section('title', 'Resumen administrativo | Santo Remedio')
@section('page-title', 'Resumen administrativo')
@section('page-subtitle', 'Indicadores generales de ventas, servicios, compras, caja e inventario')

@section('content')

<div class="card" style="margin-bottom: 22px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0; color:#4C1D95;">Resumen administrativo</h2>

            <p style="margin:6px 0 0; color:#6B7280;">
                Vista general para administración y toma de decisiones.
            </p>

            <p style="margin:6px 0 0; color:#4B5563;">
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
                href="{{ route('reportes.resumen-administrativo.exportar-csv', request()->query()) }}"
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

    <form method="GET" action="{{ route('reportes.resumen-administrativo') }}">
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

        <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn-primary">
                Aplicar filtros
            </button>

            <a href="{{ route('reportes.resumen-administrativo') }}" class="btn-secondary">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Ingresos por ventas</span>
        <h3>{{ number_format($resumen['ingresos_ventas'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Ingresos por servicios</span>
        <h3>{{ number_format($resumen['ingresos_servicios'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Ingresos totales</span>
        <h3>{{ number_format($resumen['ingresos_totales'], 2) }} Bs</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Ventas completadas</span>
        <h3>{{ $resumen['ventas_completadas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Servicios completados</span>
        <h3>{{ $resumen['servicios_completados'] }}</h3>
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

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Total compras</span>
        <h3>{{ number_format($resumen['total_compras'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Deuda proveedores</span>
        <h3>{{ number_format($resumen['deuda_proveedores'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Utilidad estimada</span>
        <h3>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</h3>
    </div>

    <div class="stat-card">
        <span>Margen estimado</span>
        <h3>{{ number_format($resumen['margen_estimado'], 2) }}%</h3>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
    <div class="stat-card">
        <span>Cajas abiertas</span>
        <h3>{{ $resumen['cajas_abiertas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Cajas cerradas</span>
        <h3>{{ $resumen['cajas_cerradas'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Productos críticos</span>
        <h3>{{ $resumen['productos_criticos'] }}</h3>
    </div>

    <div class="stat-card">
        <span>Productos agotados</span>
        <h3>{{ $resumen['productos_agotados'] }}</h3>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; color:#4C1D95;">Lectura rápida</h3>

    <div class="table-container">
        <table class="table">
            <tbody>
                <tr>
                    <th>Ingresos totales</th>
                    <td>{{ number_format($resumen['ingresos_totales'], 2) }} Bs</td>
                </tr>

                <tr>
                    <th>Total compras</th>
                    <td>{{ number_format($resumen['total_compras'], 2) }} Bs</td>
                </tr>

                <tr>
                    <th>Deuda a proveedores</th>
                    <td>{{ number_format($resumen['deuda_proveedores'], 2) }} Bs</td>
                </tr>

                <tr>
                    <th>Utilidad estimada</th>
                    <td>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</td>
                </tr>

                <tr>
                    <th>Margen estimado</th>
                    <td>{{ number_format($resumen['margen_estimado'], 2) }}%</td>
                </tr>

                <tr>
                    <th>Inventario crítico</th>
                    <td>
                        {{ $resumen['productos_criticos'] }} productos requieren atención.
                        {{ $resumen['productos_agotados'] }} están agotados.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection