@extends('layouts.app')

@section('title', 'Resumen administrativo | Santo Remedio')
@section('page-title', 'Resumen administrativo')
@section('page-subtitle', 'Indicadores generales de ventas, servicios, compras, caja e inventario')

@section('content')

<div class="report-detail-page">

    <div class="compact-card report-detail-header">
        <div>
            <h2>
                <i class="bi bi-speedometer2"></i>
                Resumen administrativo
            </h2>

            <p>
                Sucursal:
                <strong>{{ $sucursal?->nombre ?? 'Sin sucursal' }}</strong>
                |
                Periodo:
                <strong>{{ $fechaInicio }}</strong>
                al
                <strong>{{ $fechaFin }}</strong>
            </p>
        </div>

        <div class="detail-actions">
            <a
                href="{{ route('reportes.resumen-administrativo.exportar-xlsx', request()->query()) }}"
                class="btn-primary"
            >
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Excel
            </a>

            <a
                href="{{ route('reportes.resumen-administrativo.exportar-csv', request()->query()) }}"
                class="btn-secondary"
            >
                <i class="bi bi-file-earmark-excel"></i>
                CSV
            </a>

            <a href="{{ route('reportes.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Reportes
            </a>
        </div>
    </div>

    <div class="compact-card report-filter-card">
        <form method="GET" action="{{ route('reportes.resumen-administrativo') }}" class="filter-bar report-filter-bar">
            <div class="form-group">
                <label>Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
            </div>

            <div class="form-group">
                <label>Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('reportes.resumen-administrativo') }}" class="btn-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="admin-main-summary">
        <div class="admin-total-card">
            <span>Ingresos totales</span>
            <strong>{{ number_format($resumen['ingresos_totales'], 2) }} Bs</strong>
            <small>
                Ventas: {{ number_format($resumen['ingresos_ventas'], 2) }} Bs |
                Servicios: {{ number_format($resumen['ingresos_servicios'], 2) }} Bs
            </small>
        </div>

        <div class="admin-total-card admin-profit-card">
            <span>Utilidad estimada</span>
            <strong>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</strong>
            <small>Margen estimado: {{ number_format($resumen['margen_estimado'], 2) }}%</small>
        </div>

        <div class="admin-total-card admin-warning-card">
            <span>Deuda proveedores</span>
            <strong>{{ number_format($resumen['deuda_proveedores'], 2) }} Bs</strong>
            <small>Total compras: {{ number_format($resumen['total_compras'], 2) }} Bs</small>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Ventas completadas</span>
            <strong>{{ $resumen['ventas_completadas'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Servicios completados</span>
            <strong>{{ $resumen['servicios_completados'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Ventas anuladas</span>
            <strong>{{ $resumen['ventas_anuladas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Servicios anulados</span>
            <strong>{{ $resumen['servicios_anulados'] }}</strong>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Total compras</span>
            <strong>{{ number_format($resumen['total_compras'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-warning">
            <span>Deuda proveedores</span>
            <strong>{{ number_format($resumen['deuda_proveedores'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat report-mini-total">
            <span>Utilidad estimada</span>
            <strong>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</strong>
        </div>

        <div class="report-mini-stat">
            <span>Margen estimado</span>
            <strong>{{ number_format($resumen['margen_estimado'], 2) }}%</strong>
        </div>
    </div>

    <div class="report-summary-strip report-summary-four">
        <div class="report-mini-stat">
            <span>Cajas abiertas</span>
            <strong>{{ $resumen['cajas_abiertas'] }}</strong>
        </div>

        <div class="report-mini-stat">
            <span>Cajas cerradas</span>
            <strong>{{ $resumen['cajas_cerradas'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-warning">
            <span>Productos críticos</span>
            <strong>{{ $resumen['productos_criticos'] }}</strong>
        </div>

        <div class="report-mini-stat report-mini-danger">
            <span>Productos agotados</span>
            <strong>{{ $resumen['productos_agotados'] }}</strong>
        </div>
    </div>

    <div class="compact-card">
        <div class="detail-section-head">
            <h3>
                <i class="bi bi-clipboard-data"></i>
                Lectura rápida
            </h3>
        </div>

        <div class="admin-reading-grid">
            <div class="admin-reading-item">
                <span>Ingresos totales</span>
                <strong>{{ number_format($resumen['ingresos_totales'], 2) }} Bs</strong>
                <small>Ventas y servicios completados.</small>
            </div>

            <div class="admin-reading-item">
                <span>Total compras</span>
                <strong>{{ number_format($resumen['total_compras'], 2) }} Bs</strong>
                <small>Compras registradas en el rango.</small>
            </div>

            <div class="admin-reading-item admin-reading-warning">
                <span>Deuda a proveedores</span>
                <strong>{{ number_format($resumen['deuda_proveedores'], 2) }} Bs</strong>
                <small>Monto pendiente de pago.</small>
            </div>

            <div class="admin-reading-item admin-reading-success">
                <span>Utilidad estimada</span>
                <strong>{{ number_format($resumen['utilidad_estimada'], 2) }} Bs</strong>
                <small>Margen: {{ number_format($resumen['margen_estimado'], 2) }}%.</small>
            </div>

            <div class="admin-reading-item admin-reading-warning">
                <span>Inventario crítico</span>
                <strong>{{ $resumen['productos_criticos'] }} producto(s)</strong>
                <small>Requieren atención por bajo stock.</small>
            </div>

            <div class="admin-reading-item admin-reading-danger">
                <span>Productos agotados</span>
                <strong>{{ $resumen['productos_agotados'] }} producto(s)</strong>
                <small>Sin stock disponible.</small>
            </div>
        </div>
    </div>

</div>

@endsection